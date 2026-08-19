<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\OrganizationController;
use api\modules\v1\models\Organization;
use api\modules\v1\models\UserOrganization;
use api\modules\v1\services\IamAuthorizationReadService;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\web\Request;
use yii\web\Response;

final class OrganizationControllerTest extends TestCase
{
    public function testOrganizationArtifactsExposeExpectedContracts(): void
    {
        $this->assertSame('{{%organization}}', Organization::tableName());
        $this->assertSame('{{%user_organization}}', UserOrganization::tableName());

        $controller = new OrganizationController('organization', Yii::$app->getModule('v1'));
        $method = new \ReflectionMethod($controller, 'verbs');
        $verbs = $method->invoke($controller);

        $this->assertSame(['GET'], $verbs['list']);
        $this->assertSame(['POST'], $verbs['create']);
        $this->assertSame(['POST'], $verbs['update']);
        $this->assertSame(['POST'], $verbs['bind-user']);
        $this->assertSame(['POST'], $verbs['unbind-user']);
    }

    public function testSubjectBindingProbeEvidenceIsPublishedOnlyForExactDevelopRequest(): void
    {
        $originalRequest = Yii::$app->get('request');
        $hadResponse = Yii::$app->has('response');
        $originalResponse = $hadResponse ? Yii::$app->get('response') : null;
        $controller = new OrganizationController('organization', Yii::$app->getModule('v1'));
        $method = new \ReflectionMethod($controller, 'publishSubjectBindingProbeEvidence');
        $service = new class extends IamAuthorizationReadService {
            public function subjectBindingProbeEvidence(): string
            {
                return 'v1;binding=match';
            }
        };

        try {
            $request = new Request([
                'hostInfo' => 'https://api.d.xrteeth.com',
                'scriptUrl' => '',
            ]);
            $request->setQueryParams(['iamAuthzProbe' => 'wp3-subject-binding-v1']);
            $response = new Response();
            Yii::$app->set('request', $request);
            Yii::$app->set('response', $response);

            $method->invoke($controller, $service);

            $this->assertSame(
                'v1;binding=match',
                $response->headers->get('X-Identity-IAM-AuthZ-Probe-Evidence')
            );
            $this->assertSame('no-store, private', $response->headers->get('Cache-Control'));
            $this->assertSame('no-cache', $response->headers->get('Pragma'));

            foreach ([
                ['host' => 'https://d.xrugc.com', 'query' => ['iamAuthzProbe' => 'wp3-subject-binding-v1']],
                [
                    'host' => 'https://d.dev.xrugc.com',
                    'query' => ['iamAuthzProbe' => 'wp3-subject-binding-v1'],
                ],
                [
                    'host' => 'https://api.xrteeth.com',
                    'query' => ['iamAuthzProbe' => 'wp3-subject-binding-v1'],
                ],
                ['host' => 'https://api.d.xrteeth.com', 'query' => ['iamAuthzProbe' => 'wrong']],
                ['host' => 'https://api.d.xrteeth.com', 'query' => [
                    'iamAuthzProbe' => 'wp3-subject-binding-v1',
                    'extra' => '1',
                ]],
            ] as $case) {
                $request = new Request(['hostInfo' => $case['host'], 'scriptUrl' => '']);
                $request->setQueryParams($case['query']);
                $response = new Response();
                Yii::$app->set('request', $request);
                Yii::$app->set('response', $response);

                $method->invoke($controller, $service);

                $this->assertFalse($response->headers->has('X-Identity-IAM-AuthZ-Probe-Evidence'));
                $this->assertFalse($response->headers->has('Cache-Control'));
            }
        } finally {
            Yii::$app->set('request', $originalRequest);
            if ($hadResponse) {
                Yii::$app->set('response', $originalResponse);
            } else {
                Yii::$app->clear('response');
            }
        }
    }
}
