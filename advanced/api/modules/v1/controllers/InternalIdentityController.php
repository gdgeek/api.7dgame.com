<?php

namespace api\modules\v1\controllers;

use Yii;
use api\modules\v1\models\User;
use api\modules\v1\services\UserManagementService;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use api\modules\v1\services\SessionService;

class InternalIdentityController extends Controller
{
    private ?SessionService $sessionService = null;
    private ?UserManagementService $userManagementService = null;

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

    public function actionIamShadowUserInfoProbe()
    {
        $this->assertIamInternalToken();
        $this->assertShadowCompareEnabled();

        $legacyUserId = Yii::$app->request->post('legacyUserId');
        if ($legacyUserId === null) {
            $legacyUserId = Yii::$app->request->post('legacy_user_id');
        }
        if (!is_numeric($legacyUserId) || (int)$legacyUserId <= 0) {
            throw new BadRequestHttpException('legacyUserId must be a positive integer.');
        }

        $user = User::findIdentity((int)$legacyUserId);
        if ($user === null) {
            throw new NotFoundHttpException('Shadow probe subject was not found.');
        }

        $this->userManagementService()->buildCurrentUserPayload($user);

        return [
            'success' => true,
            'data' => [
                'context' => 'user.info',
                'comparisonRequested' => true,
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

    private function userManagementService(): UserManagementService
    {
        if ($this->userManagementService === null) {
            $this->userManagementService = new UserManagementService();
        }

        return $this->userManagementService;
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

    private function assertIamInternalToken(): void
    {
        $configuredToken = $this->iamInternalToken();
        if ($configuredToken === null) {
            throw new NotFoundHttpException('IAM shadow probe is not configured.');
        }

        $requestToken = Yii::$app->request->headers->get('X-Identity-Internal-Token');
        if (!is_string($requestToken) || !hash_equals($configuredToken, $requestToken)) {
            throw new ForbiddenHttpException('Invalid internal token.');
        }
    }

    private function assertShadowCompareEnabled(): void
    {
        $value = getenv('IDENTITY_IAM_SHADOW_COMPARE');
        if ($value === false && isset(Yii::$app->params['identityAuth']['IDENTITY_IAM_SHADOW_COMPARE'])) {
            $value = Yii::$app->params['identityAuth']['IDENTITY_IAM_SHADOW_COMPARE'];
        }

        $enabled = in_array(strtolower(trim((string)$value)), ['1', 'true', 'yes', 'on'], true);
        if (!$enabled) {
            throw new NotFoundHttpException('IAM shadow probe is not enabled.');
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

    private function iamInternalToken(): ?string
    {
        $value = getenv('IDENTITY_IAM_INTERNAL_API_TOKEN');
        if ($value === false || $value === null || trim((string)$value) === '') {
            $value = getenv('IDENTITY_INTERNAL_API_TOKEN');
        }
        if (($value === false || $value === null || trim((string)$value) === '') && isset(Yii::$app->params['identityAuth']['IDENTITY_IAM_INTERNAL_API_TOKEN'])) {
            $value = Yii::$app->params['identityAuth']['IDENTITY_IAM_INTERNAL_API_TOKEN'];
        }
        if (($value === false || $value === null || trim((string)$value) === '') && isset(Yii::$app->params['identityAuth']['IDENTITY_INTERNAL_API_TOKEN'])) {
            $value = Yii::$app->params['identityAuth']['IDENTITY_INTERNAL_API_TOKEN'];
        }

        $trimmed = trim((string)$value);
        return $trimmed === '' ? null : $trimmed;
    }
}
