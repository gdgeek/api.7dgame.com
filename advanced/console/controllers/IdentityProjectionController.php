<?php

namespace console\controllers;

use api\modules\v1\services\IdentityProviderClient;
use api\modules\v1\services\IdentityUserProjection;
use api\modules\v1\services\IndependentIdentity;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class IdentityProjectionController extends Controller
{
    public int $limit = 100;
    public int $pollInterval = 2;
    public int $maxBackoff = 30;
    protected bool $stopRequested = false;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), $actionID === 'watch'
            ? ['limit', 'pollInterval', 'maxBackoff'] : ['limit']);
    }

    /** Process one page; repeated invocation resumes the durable cursor. */
    public function actionSync(): int
    {
        $this->assertIndependent();
        $result = $this->syncPage();
        $this->stdout("Identity user projections applied: {$result['applied']}; cursor: {$result['cursor']}\n");
        return ExitCode::OK;
    }

    /** Supervisor-friendly durable catch-up; stop after the in-flight page commits. */
    public function actionWatch(): int
    {
        $this->assertIndependent();
        if ($this->limit < 1 || $this->limit > 500) {
            throw new \yii\console\Exception('Projection limit must be between 1 and 500.');
        }
        if ($this->pollInterval < 1 || $this->pollInterval > 60 || $this->maxBackoff < 1 || $this->maxBackoff > 300) {
            throw new \yii\console\Exception('Invalid projection worker timing.');
        }
        $this->installSignals();
        $failures = 0;
        while (!$this->stopRequested) {
            try {
                $result = $this->syncPage();
                $failures = 0;
                if ($result['received'] < $this->limit) {
                    $this->waitForNextPage($this->pollInterval);
                }
            } catch (\Throwable $exception) {
                ++$failures;
                // Do not serialize exceptions: SQL, email addresses and
                // internal HTTP credentials must not enter worker logs.
                $this->stderr("Identity projection page failed; retrying with backoff.\n");
                $this->waitForNextPage(min($this->maxBackoff, 2 ** min($failures, 8)));
            }
        }
        $this->stdout("Identity projection worker stopped.\n");
        return ExitCode::OK;
    }

    protected function syncPage(): array
    {
        if ($this->limit < 1 || $this->limit > 500) {
            throw new \yii\console\Exception('Projection limit must be between 1 and 500.');
        }
        $db = Yii::$app->has('identityProjectionDb') ? Yii::$app->get('identityProjectionDb') : Yii::$app->db;
        $afterId = (int)$db->createCommand('SELECT [[cursor]] FROM {{%identity_projection_checkpoint}} WHERE [[name]]=:name', [':name' => 'identity-users'])->queryScalar();
        $batch = (new IdentityProviderClient())->identityProjectionEvents($afterId, $this->limit);
        $events = $batch['events'] ?? null;
        $nextCursor = $batch['nextCursor'] ?? null;
        if (!is_array($events) || !is_int($nextCursor) || $nextCursor < $afterId) {
            throw new \UnexpectedValueException('Invalid Identity projection event page.');
        }
        $projection = new IdentityUserProjection();
        $applied = 0;
        foreach ($events as $event) {
            if (!is_int($event['id'] ?? null) || $event['id'] <= $afterId || $event['id'] > $nextCursor) {
                throw new \UnexpectedValueException('Invalid Identity projection event cursor.');
            }
            $applied += (int)$projection->apply($db, $event);
        }
        // A crash before this update safely replays versioned events. Parallel
        // workers cannot move the shared checkpoint backwards.
        $db->createCommand('UPDATE {{%identity_projection_checkpoint}} SET [[cursor]]=GREATEST([[cursor]], :cursor) WHERE [[name]]=:name', [
            ':cursor' => $nextCursor, ':name' => 'identity-users',
        ])->execute();
        return ['applied' => $applied, 'received' => count($events), 'cursor' => $nextCursor];
    }

    protected function installSignals(): void
    {
        if (!function_exists('pcntl_async_signals') || !function_exists('pcntl_signal')) {
            throw new \yii\console\Exception('The projection worker requires the CLI pcntl extension.');
        }
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, function (): void { $this->stopRequested = true; });
        pcntl_signal(SIGINT, function (): void { $this->stopRequested = true; });
    }

    protected function waitForNextPage(int $seconds): void
    {
        for ($elapsed = 0; $elapsed < $seconds && !$this->stopRequested; ++$elapsed) {
            sleep(1);
        }
    }

    /** Run only after the frozen final import and before reopening traffic. */
    public function actionSanitizeCredentials(): int
    {
        $this->assertIndependent();
        $db = Yii::$app->has('identityProjectionDb') ? Yii::$app->get('identityProjectionDb') : Yii::$app->db;
        $client = new IdentityProviderClient();
        // Check every existing subject before removing any local credential.
        foreach ((new \yii\db\Query())->select(['id'])->from('{{%user}}')->each(500, $db) as $row) {
            $subject = $client->identitySubject((int)$row['id']);
            if (($subject['id'] ?? null) !== (int)$row['id']) {
                throw new \UnexpectedValueException('Identity import is missing a business subject.');
            }
        }
        $available = $db->getTableSchema('{{%user}}', true)->columns;
        $values = array_intersect_key([
            'password_hash' => '!identity-owned', 'auth_key' => '',
            'password_reset_token' => null, 'verification_token' => null,
            'access_token' => null,
        ], $available);
        $count = $db->createCommand()->update('{{%user}}', $values)->execute();
        $this->stdout("Business account credentials sanitized: {$count}\n");
        return ExitCode::OK;
    }

    private function assertIndependent(): void
    {
        if (!IndependentIdentity::enabled()) {
            throw new \yii\console\Exception('Identity projection commands require IDENTITY_DATA_SOURCE=identity.');
        }
    }
}
