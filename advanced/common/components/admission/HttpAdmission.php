<?php

declare(strict_types=1);

namespace common\components\admission;

require_once __DIR__ . '/CoordinatorClient.php';

/** Same-host framework adapter; this alone is not a complete writer roster. */
final class HttpAdmission
{
    private static ?self $request = null;
    private CoordinatorClient $client;
    private ?string $capability = null;
    private bool $unknown = false;
    private bool $handlerReturned = false;
    private bool $endIssued = false;
    private bool $endUnwound = false;
    private bool $completed = false;
    private bool $entered = false;
    private int $pendingWork = 0;
    private ?\Closure $shutdownErrorGuard = null;
    private ?\Closure $shutdownExceptionGuard = null;
    private int $startedNs;
    private int $requestBudgetMs;
    private int $childWaitMs;
    private ?array $enteredAck = null;
    private bool $childrenIssued = false;
    private ?int $safetyDeadlineNs = null;
    private array $safetyReports = [];

    public static function isRunning(): bool { return self::$request !== null; }

    public static function runEntry(string $filename): void
    {
        if (self::isRunning()) throw new \RuntimeException('Identity admission nesting is invalid.');
        $flag = strtolower(trim((string) getenv('IDENTITY_DEV_ADMISSION_ENABLED')));
        if (!in_array($flag, ['1', 'true', 'yes', 'on'], true)) throw new \RuntimeException('Identity admission configuration is invalid.');
        // PHP/Yii debug stack traces must never capture the trusted key array.
        ini_set('zend.exception_ignore_args', '1');
        if (!filter_var(ini_get('zend.exception_ignore_args'), FILTER_VALIDATE_BOOLEAN)) throw new \RuntimeException('Identity admission runtime is unsupported.');
        unset($_SERVER['IDENTITY_DEV_ADMISSION_KEY'], $_ENV['IDENTITY_DEV_ADMISSION_KEY']);
        $request = new self(); $request->startedNs = hrtime(true); self::$request = $request;
        register_shutdown_function([$request, 'shutdown']);
        try {
            $request->requestBudgetMs = self::configuredBudget('IDENTITY_DEV_ADMISSION_REQUEST_BUDGET_MS');
            $request->childWaitMs = self::configuredBudget('IDENTITY_DEV_ADMISSION_CHILD_WAIT_MS');
            $request->client = CoordinatorClient::fromEnvironment(); $request->client->register($request->requestControlBudget());
            $request->requestControlBudget();
            if (!in_array(PHP_SAPI, ['apache2handler', 'cli-server'], true)) throw new \RuntimeException('Unreviewed PHP request lifecycle.');
            if (session_status() === PHP_SESSION_ACTIVE || filter_var(ini_get('session.auto_start'), FILTER_VALIDATE_BOOLEAN)) throw new \RuntimeException('Session initialized before identity admission.');
            $provided = $_SERVER['HTTP_X_IDENTITY_ADMISSION_LEASE'] ?? null;
            if ($provided !== null) {
                try { CoordinatorClient::capability($provided); }
                catch (\Throwable) { throw new ControlRefusal('MALFORMED_CAPABILITY'); }
                $request->capability = $provided;
            }
            else $request->capability = $request->client->begin($request->requestControlBudget());
            $request->enteredAck = $request->client->call('enter', ['capability' => $request->capability], $request->requestControlBudget());
            $request->requestControlBudget();
            $request->entered = true;
            // A disconnected browser must not terminate PHP between a commit,
            // compensation and session write. Do not restore until shutdown.
            ignore_user_abort(true);
            $execute = static function (string $entry): void { require $entry; };
            try { $execute($filename); $request->handlerReturned = true; }
            catch (AdmissionEnd $end) {
                if (!$request->endIssued) { $request->markUnknown(); throw $end; }
                $request->endUnwound = true; $request->handlerReturned = true;
            }
            if ($request->endIssued && !$request->endUnwound) $request->markUnknown();
            if (!$request->finishSynchronousWork()) $request->markUnknown();
        } catch (\Throwable $error) {
            if ($error instanceof ControlRefusal && !$request->entered) {
                $request->unknown = true; // Explicit refusal never ran this handler; not a lost transport ACK.
                throw $error;
            }
            $request->markUnknown(); throw $error;
        }
        // Completion is intentionally deferred beyond Yii's ordinary shutdown
        // callbacks. response.send/afterRequest is never the completion event.
    }

    public static function applicationClass(): string
    {
        if (!self::isRunning()) return \yii\web\Application::class;
        require_once __DIR__ . '/WebApplication.php'; return WebApplication::class;
    }

    public static function endRequested(): void
    {
        $request = self::$request;
        if ($request === null) throw new \RuntimeException('Identity admission is not active.');
        if ($request->endIssued) $request->markUnknown();
        $request->endIssued = true;
    }

    /** Reentrant same-request work; exceptions are never silently completed. */
    public static function work(callable $operation): mixed
    {
        $request = self::$request;
        if ($request === null) return $operation();
        if ($request->unknown || $request->completed) { $request->markUnknown(); throw new \RuntimeException('Identity admission result is unknown.'); }
        ++$request->pendingWork;
        try { return $operation(); }
        catch (\Throwable $error) { $request->markUnknown(); throw $error; }
        finally { --$request->pendingWork; }
    }

    /** Actual remote handler must enter and complete this child capability. */
    public static function continuationHeaders(string $targetId): array
    {
        $request = self::$request;
        if ($request === null) return [];
        if ($request->unknown || $request->completed) throw new \RuntimeException('Identity admission result is unknown.');
        $childCapability = null;
        try {
            $child = $request->client->call('continue', ['capability' => $request->capability, 'targetId' => $targetId], $request->requestControlBudget());
            $childCapability = $child['capability']; $request->childrenIssued = true;
            $request->requestControlBudget(); // The ACK cannot renew the original arrival deadline.
            return ['X-Identity-Admission-Lease' => $childCapability];
        } catch (\Throwable $error) {
            $request->markUnknown();
            if ($childCapability !== null) $request->reportSafety('unknown', ['capability' => $childCapability]);
            throw $error;
        }
    }

    public static function unknownResult(): void { self::$request?->markUnknown(); }

    public static function closePhpSession(): bool
    {
        $ok = true;
        if (session_status() === PHP_SESSION_ACTIVE) {
            set_error_handler(static function () use (&$ok): bool { $ok = false; return true; });
            try { if (!session_write_close()) $ok = false; }
            catch (\Throwable) { $ok = false; }
            finally { restore_error_handler(); }
        }
        if (session_status() === PHP_SESSION_ACTIVE) $ok = false;
        if (!$ok) self::unknownResult();
        return $ok;
    }

    private function markUnknown(): void
    {
        if ($this->unknown) return;
        $this->unknown = true;
        if (!isset($this->client)) return;
        $action = $this->capability === null || $this->completed ? 'lost' : 'unknown';
        if (!$this->reportSafety($action, $action === 'lost' ? [] : ['capability' => $this->capability]) && $action !== 'lost') $this->reportSafety('lost');
    }

    /** A shared bounded reporting tail; never described as inside the user budget. */
    private function reportSafety(string $action, array $payload = []): bool
    {
        $id = $action . ':' . ($payload['capability'] ?? '');
        if (isset($this->safetyReports[$id]) || !isset($this->client)) return false;
        $this->safetyReports[$id] = true;
        $this->safetyDeadlineNs ??= hrtime(true) + 250000000;
        $remaining = (int) floor(($this->safetyDeadlineNs - hrtime(true)) / 1000000);
        if ($remaining < 1) return false;
        try { $this->client->call($action, $payload, min(100, $remaining)); return true; }
        catch (\Throwable) { return false; } // Irreversible local unknown, no mutation retry or fake zero.
    }

    private static function configuredBudget(string $name): int
    {
        $value = getenv($name); if ($value === false || $value === '') return 1500;
        if (!preg_match('/^[1-9][0-9]{1,3}$/D', $value) || (int) $value < 25 || (int) $value > 5000) throw new \RuntimeException('Identity admission budget is invalid.');
        return (int) $value;
    }

    private function requestControlBudget(): int
    {
        $remaining = (int) floor(($this->startedNs + $this->requestBudgetMs * 1000000 - hrtime(true)) / 1000000);
        if ($remaining < 1) throw new \RuntimeException('Identity admission budget is exhausted.');
        return min(2000, $remaining);
    }

    private function finishSynchronousWork(): bool
    {
        if ($this->unknown || $this->pendingWork !== 0) return false;
        $ok = self::closePhpSession();
        // Yii Session::close suppresses write warnings in prod and returns no
        // result. Observe the actual PHP write/close first, including warnings.
        if (class_exists('Yii', false) && \Yii::$app !== null) {
            $handler = set_error_handler(static function (): bool { return false; }); restore_error_handler();
            $exceptionHandler = set_exception_handler(static function (): void {}); restore_exception_handler();
            $expected = \Yii::$app->getErrorHandler();
            if (($handler !== null && $handler !== [$expected, 'handleError'] && $handler !== $this->shutdownErrorGuard)
                || ($exceptionHandler !== null && $exceptionHandler !== [$expected, 'handleException'] && $exceptionHandler !== $this->shutdownExceptionGuard)) $ok = false;
            if (\Yii::$app->has('session', true)) {
                try { \Yii::$app->get('session')->close(); } catch (\Throwable) { $ok = false; }
            }
            try { \Yii::getLogger()->flush(true); } catch (\Throwable) { $ok = false; }
            // A log target may itself use a DB or session. Observe their final
            // state after the actual export, not merely before calling flush.
            if (!self::closePhpSession()) $ok = false;
            foreach (\Yii::$app->getComponents(false) as $component) {
                if ($component instanceof \yii\db\Connection && $component->getTransaction()?->getIsActive()) $ok = false;
            }
        }
        return $ok;
    }

    public function shutdown(): void
    {
        if (!$this->handlerReturned || $this->fatal() || $this->unknown) { $this->markUnknown(); return; }
        $previousError = null; $previousException = null;
        $this->shutdownErrorGuard = function (...$arguments) use (&$previousError): bool {
            $this->markUnknown(); return $previousError === null ? false : (bool) $previousError(...$arguments);
        };
        $this->shutdownExceptionGuard = function (\Throwable $error) use (&$previousException): void {
            $this->markUnknown(); if ($previousException !== null) $previousException($error);
        };
        $previousError = set_error_handler($this->shutdownErrorGuard);
        $previousException = set_exception_handler($this->shutdownExceptionGuard);
        // Yii Logger's first shutdown handler itself schedules a final flush.
        // Two tail stages place this adapter after that reviewed framework
        // callback and the Session callback, then verify/close/flush once more.
        register_shutdown_function(function (): void {
            register_shutdown_function(function (): void { $this->completeAfterShutdownCallbacks(); });
        });
    }

    private function completeAfterShutdownCallbacks(): void
    {
        if ($this->unknown || $this->completed) return;
        if ($this->fatal() || !$this->handlerReturned || !$this->finishSynchronousWork()) { $this->markUnknown(); return; }
        try {
            if (!$this->waitForActualChildren()) { $this->markUnknown(); return; }
            $this->client->call('complete', ['capability' => $this->capability, 'evidence' => ['handlerFinished' => true, 'sideEffectsFinished' => true, 'sessionWriteClosed' => true]]);
            $this->completed = true;
        } catch (\Throwable) { $this->markUnknown(); }
    }

    private function waitForActualChildren(): bool
    {
        $end = min(hrtime(true) + $this->childWaitMs * 1000000, $this->startedNs + $this->requestBudgetMs * 1000000);
        do {
            $remaining = (int) floor(($end - hrtime(true)) / 1000000);
            if ($this->childrenIssued && $remaining < 1) return false;
            // An already finished long handler with no issued child may prove
            // zero once. It never extends a known pending child's deadline.
            $budget = $remaining < 1 ? 25 : min(2000, $remaining);
            $actual = $this->client->call('validate', ['capability' => $this->capability], $budget);
            if (($actual['unknown'] ?? true) !== false || $this->enteredAck === null) return false;
            foreach (['operationId', 'rootOperationId', 'epoch'] as $key) if ($actual[$key] !== $this->enteredAck[$key]) return false;
            if ($this->childrenIssued && hrtime(true) >= $end) return false;
            if ($actual['childrenActive'] === 0) return true;
            if (!$this->childrenIssued) return false;
            $remaining = (int) floor(($end - hrtime(true)) / 1000000); if ($remaining <= 25) return false;
            usleep(25000);
        } while (!$this->unknown);
        return false;
    }

    private function fatal(): bool
    {
        $last = error_get_last();
        return $last !== null && in_array($last['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR], true);
    }
}

final class AdmissionEnd extends \RuntimeException {}
