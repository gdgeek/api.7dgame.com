<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use api\modules\v1\services\SessionService;

class InternalIdentityController extends Controller
{
    private ?SessionService $sessionService = null;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator'], $behaviors['access']);

        return $behaviors;
    }

    public function actionRevokeSessions()
    {
        $this->assertInternalToken();

        $legacyUserId = Yii::$app->request->post('legacyUserId');
        if ($legacyUserId === null) {
            $legacyUserId = Yii::$app->request->post('legacy_user_id');
        }
        if (!is_numeric($legacyUserId) || (int)$legacyUserId <= 0) {
            throw new BadRequestHttpException('legacyUserId must be a positive integer.');
        }

        $revoked = $this->sessionService()->revokeUserSessions((int)$legacyUserId);

        return [
            'success' => true,
            'data' => [
                'legacy_user_id' => (int)$legacyUserId,
                'revoked' => $revoked,
            ],
        ];
    }

    private function sessionService(): SessionService
    {
        if ($this->sessionService === null) {
            $this->sessionService = new SessionService();
        }

        return $this->sessionService;
    }

    private function assertInternalToken(): void
    {
        $configuredToken = $this->internalToken();
        if ($configuredToken === null) {
            throw new NotFoundHttpException('Internal identity endpoint is not configured.');
        }

        $requestToken = Yii::$app->request->headers->get('X-Identity-Internal-Token');
        if (!is_string($requestToken) || !hash_equals($configuredToken, $requestToken)) {
            throw new ForbiddenHttpException('Invalid internal token.');
        }
    }

    private function internalToken(): ?string
    {
        $value = getenv('IDENTITY_ACCOUNT_INTERNAL_TOKEN');
        if ($value === false || $value === null || trim((string)$value) === '') {
            $value = getenv('IDENTITY_INTERNAL_API_TOKEN');
        }
        if (($value === false || $value === null || trim((string)$value) === '') && isset(Yii::$app->params['identityAuth']['IDENTITY_ACCOUNT_INTERNAL_TOKEN'])) {
            $value = Yii::$app->params['identityAuth']['IDENTITY_ACCOUNT_INTERNAL_TOKEN'];
        }
        if (($value === false || $value === null || trim((string)$value) === '') && isset(Yii::$app->params['identityAuth']['IDENTITY_INTERNAL_API_TOKEN'])) {
            $value = Yii::$app->params['identityAuth']['IDENTITY_INTERNAL_API_TOKEN'];
        }

        $trimmed = trim((string)$value);
        return $trimmed === '' ? null : $trimmed;
    }
}
