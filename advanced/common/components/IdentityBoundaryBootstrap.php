<?php

namespace common\components;

use api\modules\v1\filters\IdentityJwtHttpBearerAuth;
use api\modules\v1\services\IdentityAuthorityService;
use api\modules\v1\services\IndependentIdentity;
use api\modules\v1\services\IdentityRouteGateway;
use Yii;
use yii\base\ActionEvent;
use yii\base\BootstrapInterface;
use yii\base\Component;

class IdentityBoundaryBootstrap extends Component implements BootstrapInterface
{
    public function bootstrap($app)
    {
        IndependentIdentity::assertHttpReady();
        // Bind even in legacy mode so enabling the ownership switch cannot
        // leave an individual controller using a local-only JWT verifier.
        Yii::$container->set(\bizley\jwt\JwtHttpBearerAuth::class, IdentityJwtHttpBearerAuth::class);
        $app->set('identityAuthority', ['class' => IdentityAuthorityService::class]);
        $app->on(\yii\base\Module::EVENT_BEFORE_ACTION, function (ActionEvent $event) use ($app): void {
            if (!IndependentIdentity::enabled()) {
                return;
            }
            $gateway = new IdentityRouteGateway();
            $result = $gateway->forward($event->action->uniqueId);
            if ($result !== null) {
                $app->response->format = \yii\web\Response::FORMAT_JSON;
                $app->response->statusCode = $result['status'];
                $app->response->data = $result['body'];
                foreach (($result['headers'] ?? []) as $name => $value) {
                    if (preg_match('/^X-Pagination-(Total-Count|Page-Count|Current-Page|Per-Page)$/i', $name) === 1) {
                        $app->response->headers->set($name, $value);
                    }
                }
                $event->isValid = false;
            }
        });
    }
}
