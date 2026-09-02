<?php

namespace api\modules\v1\controllers;

use api\modules\v1\services\DbTask51StageBRepository;
use api\modules\v1\services\Task51ArtifactException;
use api\modules\v1\services\Task51CanonicalArtifact;
use api\modules\v1\services\Task51CoordinatorException;
use api\modules\v1\services\Task51StageBCoordinatorService;
use api\modules\v1\services\Task51StageBSettings;
use bizley\jwt\JwtHttpBearerAuth;
use mdm\admin\components\AccessControl;
use Yii;
use yii\db\Command;
use yii\db\Connection;
use yii\filters\auth\CompositeAuth;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\GoneHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/** Default-off control plane; no raw artifact, capability, or token is logged. */
final class Task51StageBController extends Controller
{
    private const INTERNAL_TOKEN_HEADER = 'X-Task51-Internal-Token';
    private const CLAIM_CAPABILITY_HEADER = 'X-Task51-Claim-Capability';
    private const RUNNER_EXPORT_REF_HEADER = 'X-Task51-Runner-Export-Receipt-Ref';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [JwtHttpBearerAuth::class],
            // These actions use a dedicated capability or internal-token
            // authentication domain and must never accept a user JWT instead.
            'except' => ['issue', 'claim', 'consume', 'options'],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'allowActions' => ['issue', 'claim', 'consume', 'options'],
        ];

        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'issue' => ['POST'],
            'claim' => ['POST'],
            'consume' => ['POST'],
        ];
    }

    public function actionIssue(): array
    {
        $this->applySensitiveResponseHeaders();
        $settings = $this->assertReady();
        $this->assertInternalToken($settings);
        try {
            $metadata = $this->service($settings)->issue(
                $this->requiredRawBody(Task51CanonicalArtifact::MAX_STAGE_B_BYTES),
                $this->requiredHeader(self::CLAIM_CAPABILITY_HEADER)
            );
        } catch (Task51ArtifactException|Task51CoordinatorException $exception) {
            $this->throwHttpException($exception);
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        return ['success' => true, 'data' => $metadata];
    }

    public function actionClaim(): string
    {
        $this->applySensitiveResponseHeaders();
        $settings = $this->assertReady();
        if (Yii::$app->request->headers->get('Origin') !== Task51StageBSettings::CLAIM_ORIGIN) {
            throw new ForbiddenHttpException('Claim origin is not allowed.');
        }
        try {
            $receipt = $this->service($settings)->claim(
                $this->requiredRawBody(Task51CanonicalArtifact::MAX_STAGE_B_BYTES),
                $this->requiredHeader(self::CLAIM_CAPABILITY_HEADER)
            );
        } catch (Task51ArtifactException|Task51CoordinatorException $exception) {
            $this->throwHttpException($exception);
        }
        return $this->canonicalResponse($receipt);
    }

    public function actionConsume(): string
    {
        $this->applySensitiveResponseHeaders();
        $settings = $this->assertReady();
        $this->assertInternalToken($settings);
        try {
            $receipt = $this->service($settings)->consume(
                $this->requiredRawBody(Task51CanonicalArtifact::MAX_RUNNER_EXPORT_BYTES),
                $this->requiredHeader(self::RUNNER_EXPORT_REF_HEADER)
            );
        } catch (Task51ArtifactException|Task51CoordinatorException $exception) {
            $this->throwHttpException($exception);
        }
        return $this->canonicalResponse($receipt);
    }

    private function assertReady(): Task51StageBSettings
    {
        $settings = new Task51StageBSettings();
        if (!$settings->isReady()
            || !$settings->isCanonicalRequestHost(Yii::$app->request->headers->get('Host'))
            || Yii::$app->hasModule('debug')
            || Yii::$app->hasModule('gii')) {
            throw new NotFoundHttpException('Task 5.1 Stage B coordinator is not available.');
        }
        return $settings;
    }

    private function assertInternalToken(Task51StageBSettings $settings): void
    {
        $configured = $settings->internalToken();
        if ($configured === null) {
            throw new NotFoundHttpException('Task 5.1 internal endpoint is not configured.');
        }
        $provided = Yii::$app->request->headers->get(self::INTERNAL_TOKEN_HEADER);
        if (!is_string($provided) || !hash_equals($configured, $provided)) {
            throw new ForbiddenHttpException('Invalid internal token.');
        }
    }

    private function requiredHeader(string $name): string
    {
        $value = Yii::$app->request->headers->get($name);
        if (!is_string($value) || $value === '') {
            throw new BadRequestHttpException('Required control-plane header is missing.');
        }
        return $value;
    }

    private function requiredRawBody(int $maximumBytes): string
    {
        $declared = Yii::$app->request->headers->get('Content-Length');
        if ($declared !== null
            && (!is_string($declared) || preg_match('/^[0-9]+$/D', $declared) !== 1
                || (int)$declared > $maximumBytes)) {
            throw new BadRequestHttpException('Task 5.1 request body is invalid.');
        }
        $raw = Yii::$app->request->getRawBody();
        if ($raw === '' || strlen($raw) > $maximumBytes) {
            throw new BadRequestHttpException('Task 5.1 request body is invalid.');
        }
        return $raw;
    }

    private function service(Task51StageBSettings $settings): Task51StageBCoordinatorService
    {
        $db = Yii::$app->get('task51CoordinatorDb');
        if (!$db instanceof Connection
            || get_class($db) !== Connection::class
            || $db->commandClass !== Command::class
            || $db->enableSlaves !== false) {
            throw new ServerErrorHttpException('Task 5.1 coordinator database is unavailable.');
        }
        return new Task51StageBCoordinatorService(
            new DbTask51StageBRepository($db),
            (string)$settings->serverPublishSha()
        );
    }

    private function applySensitiveResponseHeaders(): void
    {
        $headers = Yii::$app->response->headers;
        $headers->set('Cache-Control', 'no-store, private, max-age=0');
        $headers->set('Pragma', 'no-cache');
        $headers->set('Expires', '0');
        $headers->set('X-Content-Type-Options', 'nosniff');
    }

    private function canonicalResponse(string $receipt): string
    {
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $receipt;
    }

    private function throwHttpException(Task51ArtifactException|Task51CoordinatorException $exception): never
    {
        if ($exception instanceof Task51ArtifactException
            || $exception->reason() === Task51CoordinatorException::INVALID) {
            throw new BadRequestHttpException('Task 5.1 artifact rejected.');
        }
        if ($exception->reason() === Task51CoordinatorException::EXPIRED) {
            throw new GoneHttpException('Task 5.1 execution window is no longer current.');
        }
        if ($exception->reason() === Task51CoordinatorException::CONFLICT) {
            throw new ConflictHttpException('Task 5.1 exact-one transition was rejected.');
        }
        throw new ServerErrorHttpException('Task 5.1 coordinator is unavailable.');
    }
}
