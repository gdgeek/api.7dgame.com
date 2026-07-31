<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\VerseController;
use api\modules\v1\controllers\TokenController;
use api\modules\v1\models\Verse;
use api\modules\v1\models\VerseSearch;
use bizley\jwt\JwtHttpBearerAuth;
use common\components\security\CorsOriginPolicy;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\db\Connection;
use yii\db\ActiveQuery;
use yii\base\InlineAction;
use yii\filters\auth\CompositeAuth;
use yii\web\ForbiddenHttpException;
use yii\web\IdentityInterface;
use yii\web\Request;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

final class VersePreviewContractTest extends TestCase
{
    private string|false $originalCorsAllowedOrigins;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalCorsAllowedOrigins = getenv('CORS_ALLOWED_ORIGINS');
        putenv(
            'CORS_ALLOWED_ORIGINS=https://preview.example.com, https://host.example.com'
        );
    }

    protected function tearDown(): void
    {
        if ($this->originalCorsAllowedOrigins === false) {
            putenv('CORS_ALLOWED_ORIGINS');
        } else {
            putenv('CORS_ALLOWED_ORIGINS=' . $this->originalCorsAllowedOrigins);
        }
        parent::tearDown();
    }

    public function testVerseEndpointsRequireJwtAuthentication(): void
    {
        $behaviors = $this->createController()->behaviors();

        $this->assertSame(CompositeAuth::class, $behaviors['authenticator']['class']);
        $this->assertSame(
            [
                'class' => JwtHttpBearerAuth::class,
                'throwException' => false,
            ],
            $behaviors['authenticator']['authMethods'][0]
        );
        $this->assertSame(['options'], $behaviors['authenticator']['except']);
    }

    public function testMalformedJwtConvergesOnUnauthorizedResponse(): void
    {
        $authConfig = $this->createController()->behaviors()['authenticator'];
        /** @var CompositeAuth $auth */
        $auth = Yii::createObject($authConfig);
        $request = new Request([
            'hostInfo' => 'https://api.example.com',
            'scriptUrl' => '',
        ]);
        $request->getHeaders()->set('Authorization', 'Bearer not-a-jwt');
        $response = new Response();

        $this->assertNull(
            $auth->authenticate(Yii::$app->user, $request, $response),
            'Malformed JWT must not leak a parser exception as HTTP 500'
        );

        $this->expectException(UnauthorizedHttpException::class);
        $auth->handleFailure($response);
    }

    public function testVerseCorsUsesExplicitOriginsAndPaginationHeaders(): void
    {
        $cors = $this->createController()->behaviors()['corsFilter']['cors'];

        $this->assertSame(
            ['https://preview.example.com', 'https://host.example.com'],
            $cors['Origin']
        );
        $this->assertNotContains('*', $cors['Origin']);
        $this->assertContains('Authorization', $cors['Access-Control-Request-Headers']);
        $this->assertNotContains('*', $cors['Access-Control-Request-Headers']);
        $this->assertSame(
            [
                'X-Pagination-Total-Count',
                'X-Pagination-Page-Count',
                'X-Pagination-Current-Page',
                'X-Pagination-Per-Page',
                'X-Identity-IAM-Role-Write',
                'X-Identity-IAM-Role-Write-Decision',
                'X-Identity-IAM-Role-Write-Correlation',
                'X-Identity-IAM-Role-Write-Route',
                'X-Identity-IAM-Role-Write-Entry',
                'X-Identity-IAM-Role-Write-Actor',
                'X-Identity-IAM-Role-Write-Selector-Kind',
            ],
            $cors['Access-Control-Expose-Headers']
        );
    }

    public function testPublishedApiCorsUsesTheSameExplicitAllowlist(): void
    {
        $config = require dirname(__DIR__, 4) . '/files/api/config/main.php';
        $cors = $config['as cors']['cors'];

        $this->assertSame(
            ['https://preview.example.com', 'https://host.example.com'],
            $cors['Origin']
        );
        $this->assertNotContains('*', $cors['Access-Control-Request-Headers']);
        $this->assertContains('Authorization', $cors['Access-Control-Request-Headers']);
        $this->assertSame(
            [CorsOriginPolicy::class, 'enforceResponseEvent'],
            $config['components']['response']['on beforeSend']
        );
    }

    public function testCorsPolicyRejectsWildcardCredentialsPathsAndRemoteHttp(): void
    {
        $unsafeConfig = implode(',', [
            '*',
            'https://user:secret@preview.example.com',
            'https://preview.example.com/path',
            'https://preview.example.com?query=1',
            'http://preview.example.com',
            'javascript://preview.example.com',
            'http://[[::1]]:3006',
            'http://[127.0.0.1]:3006',
            'http://localhost:+3006',
            'https://safe.example.com:1x',
            'https://127.1',
            'HTTP://LOCALHOST:3006',
            'http://127.0.0.1:3006',
            'http://[::1]:3006',
            'https://SAFE.example.com:443',
            'https://safe.example.com',
        ]);

        $this->assertSame(
            [
                'http://localhost:3006',
                'http://127.0.0.1:3006',
                'http://[::1]:3006',
                'https://safe.example.com',
            ],
            CorsOriginPolicy::fromEnvironment($unsafeConfig)
        );

        putenv('CORS_ALLOWED_ORIGINS=' . $unsafeConfig);
        $controllerCors = $this->createController()->behaviors()['corsFilter']['cors'];
        $publishedConfig = require dirname(__DIR__, 4) . '/files/api/config/main.php';
        $this->assertSame(
            $controllerCors['Origin'],
            $publishedConfig['as cors']['cors']['Origin']
        );
        $this->assertNotContains('*', $controllerCors['Origin']);
    }

    public function testFinalResponseGuardOverridesLegacyControllerWildcardCors(): void
    {
        $config = require dirname(__DIR__, 4) . '/files/api/config/main.php';
        $originalRequest = Yii::$app->get('request');
        $originalServer = [];
        foreach (['HTTP_ORIGIN', 'REQUEST_METHOD'] as $key) {
            $originalServer[$key] = $_SERVER[$key] ?? null;
        }

        try {
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_SERVER['HTTP_ORIGIN'] = 'https://evil.example.com';
            $request = new Request([
                'hostInfo' => 'https://api.example.com',
                'scriptUrl' => '',
            ]);
            Yii::$app->set('request', $request);

            /** @var Response $response */
            $response = Yii::createObject($config['components']['response']);
            $controller = new TokenController('token', Yii::$app);
            $legacyCors = Yii::createObject(array_merge(
                $controller->behaviors()['corsFilter'],
                ['request' => $request, 'response' => $response]
            ));
            $legacyCors->beforeAction(new InlineAction('index', $controller, 'actionIndex'));
            $this->assertSame('*', $response->getHeaders()->get('Access-Control-Allow-Origin'));
            $response->getHeaders()->set('Access-Control-Allow-Credentials', 'true');
            $response->getHeaders()->set('Access-Control-Allow-Methods', 'GET, TRACE');
            $response->getHeaders()->set('Access-Control-Allow-Headers', '*');
            $response->getHeaders()->set('Access-Control-Max-Age', '999999');

            $response->trigger(Response::EVENT_BEFORE_SEND);
            foreach ([
                'Access-Control-Allow-Origin',
                'Access-Control-Allow-Credentials',
                'Access-Control-Allow-Methods',
                'Access-Control-Allow-Headers',
                'Access-Control-Max-Age',
                'Access-Control-Expose-Headers',
            ] as $headerName) {
                $this->assertFalse($response->getHeaders()->has($headerName));
            }
            $this->assertSame('Origin', $response->getHeaders()->get('Vary'));

            $_SERVER['HTTP_ORIGIN'] = 'https://preview.example.com';
            $request->getHeaders()->set('Origin', 'https://preview.example.com');
            /** @var Response $allowedResponse */
            $allowedResponse = Yii::createObject($config['components']['response']);
            $allowedResponse->getHeaders()->set('Access-Control-Allow-Origin', '*');
            $allowedResponse->getHeaders()->set('Vary', 'Accept-Encoding');
            $allowedResponse->getHeaders()->add('Vary', 'User-Agent, Accept-Encoding');
            $allowedResponse->trigger(Response::EVENT_BEFORE_SEND);
            $this->assertSame(
                'https://preview.example.com',
                $allowedResponse->getHeaders()->get('Access-Control-Allow-Origin')
            );
            $this->assertFalse(
                $allowedResponse->getHeaders()->has('Access-Control-Allow-Credentials')
            );
            $this->assertSame(
                'Accept-Encoding, User-Agent, Origin',
                $allowedResponse->getHeaders()->get('Vary')
            );
        } finally {
            Yii::$app->set('request', $originalRequest);
            foreach ($originalServer as $key => $value) {
                if ($value === null) {
                    unset($_SERVER[$key]);
                } else {
                    $_SERVER[$key] = $value;
                }
            }
        }
    }

    public function testFinalResponseGuardRestrictsAllowedPreflightHeaders(): void
    {
        $originalMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        try {
            $request = new Request([
                'hostInfo' => 'https://api.example.com',
                'scriptUrl' => '',
            ]);
            $request->getHeaders()->set('Origin', 'https://preview.example.com');
            $request->getHeaders()->set('Access-Control-Request-Method', 'POST');
            $request->getHeaders()->set(
                'Access-Control-Request-Headers',
                'Authorization, X-Evil'
            );
            $response = new Response();
            $response->getHeaders()->set('Access-Control-Allow-Origin', '*');
            $response->getHeaders()->set('Access-Control-Allow-Headers', '*');

            CorsOriginPolicy::enforceResponse($request, $response);

            $this->assertSame(
                'https://preview.example.com',
                $response->getHeaders()->get('Access-Control-Allow-Origin')
            );
            $this->assertSame(
                'Authorization',
                $response->getHeaders()->get('Access-Control-Allow-Headers')
            );
            $this->assertStringNotContainsString(
                'X-Evil',
                (string) $response->getHeaders()->get('Access-Control-Allow-Headers')
            );
            $this->assertSame('86400', $response->getHeaders()->get('Access-Control-Max-Age'));

            $request->getHeaders()->set('Access-Control-Request-Method', 'TRACE');
            $deniedResponse = new Response();
            $deniedResponse->getHeaders()->set('Access-Control-Allow-Origin', '*');
            CorsOriginPolicy::enforceResponse($request, $deniedResponse);
            $this->assertFalse(
                $deniedResponse->getHeaders()->has('Access-Control-Allow-Origin')
            );
        } finally {
            if ($originalMethod === null) {
                unset($_SERVER['REQUEST_METHOD']);
            } else {
                $_SERVER['REQUEST_METHOD'] = $originalMethod;
            }
        }
    }

    public function testServerComposeDefaultsIncludeStandalonePreviewOrigins(): void
    {
        $serverCompose = (string) file_get_contents(dirname(__DIR__, 4) . '/docker-compose.yml');

        foreach (['http://localhost:3006', 'http://127.0.0.1:3006'] as $origin) {
            $this->assertStringContainsString($origin, $serverCompose);
        }
    }

    public function testMyScenesAlwaysAddsTheAuthenticatedAuthorBoundary(): void
    {
        $query = new ActiveQuery(Verse::class);
        $this->createController()->applyCurrentUserFilterForTest($query, 42);

        $this->assertSame(['verse.author_id' => 42], $query->where);
    }

    public function testDifferentAuthenticatedUsersReceiveIndependentBoundaries(): void
    {
        $firstUserQuery = new ActiveQuery(Verse::class);
        $secondUserQuery = new ActiveQuery(Verse::class);
        $controller = $this->createController();

        $controller->applyCurrentUserFilterForTest($firstUserQuery, 42);
        $controller->applyCurrentUserFilterForTest($secondUserQuery, 84);

        $this->assertSame(['verse.author_id' => 42], $firstUserQuery->where);
        $this->assertSame(['verse.author_id' => 84], $secondUserQuery->where);
        $this->assertNotSame($firstUserQuery->where, $secondUserQuery->where);
    }

    public function testClientAuthorFilterCannotReplaceAuthenticatedBoundary(): void
    {
        $query = new ActiveQuery(Verse::class);
        $query->andWhere(['author_id' => 999]);

        $this->createController()->applyCurrentUserFilterForTest($query, 42);

        $this->assertSame(
            [
                'and',
                ['author_id' => 999],
                ['verse.author_id' => 42],
            ],
            $query->where
        );
    }

    public function testProductionActionIndexQueriesOnlyTheAuthenticatedUsersRows(): void
    {
        $this->withPreviewDatabase(function (Connection $db): void {
            $db->createCommand()->batchInsert(
                'verse',
                ['id', 'author_id', 'name', 'info', 'data', 'created_at', 'updated_at'],
                [
                    [1, 42, 'Owner scene', '', '{}', 100, 300],
                    [2, 84, 'Other scene', '', '{}', 200, 400],
                    [3, 42, 'Owner second', '', '{}', 300, 500],
                ]
            )->execute();

            $this->switchPreviewIdentity(42);
            Yii::$app->request->setQueryParams([
                'VerseSearch' => ['name' => 'Owner'],
                'sort' => '-updated_at',
                'page' => 1,
                'per-page' => 20,
            ]);
            $provider = $this->createController()->actionIndex();
            $rows = $provider->query->orderBy(['id' => SORT_ASC])->asArray()->all();
            $this->assertSame([1, 3], array_map('intval', array_column($rows, 'id')));
            $this->assertSame([42, 42], array_map('intval', array_column($rows, 'author_id')));

            Yii::$app->request->setQueryParams([
                'VerseSearch' => ['author_id' => 84],
            ]);
            $attackerProvider = $this->createController()->actionIndex();
            $this->assertSame([], $attackerProvider->query->asArray()->all());

            $this->switchPreviewIdentity(84);
            Yii::$app->request->setQueryParams([]);
            $otherProvider = $this->createController()->actionIndex();
            $otherRows = $otherProvider->query->asArray()->all();
            $this->assertSame([2], array_map('intval', array_column($otherRows, 'id')));
            $this->assertSame([84], array_map('intval', array_column($otherRows, 'author_id')));
        });
    }

    public function testMyScenesNameFilterRemainsSupported(): void
    {
        $rules = (new VerseSearch())->rules();
        $safeFields = [];
        foreach ($rules as $rule) {
            if (($rule[1] ?? null) === 'safe') {
                $safeFields = array_merge($safeFields, $rule[0]);
            }
        }
        $this->assertContains('name', $safeFields);

        $source = (string) file_get_contents(
            dirname(__DIR__, 3) . '/api/modules/v1/models/VerseSearch.php'
        );
        $this->assertStringContainsString(
            "->andFilterWhere(['like', 'name', \$this->name])",
            $source
        );
    }

    public function testExistingDetailModelExposesUnityPreviewExpansions(): void
    {
        $extraFields = (new Verse())->extraFields();

        foreach (['metas', 'resources', 'verseCode', 'js', 'lua', 'image'] as $field) {
            $this->assertContains($field, $extraFields);
        }
    }

    public function testDetailAccessRejectsNonViewableScenes(): void
    {
        $controller = $this->createController();
        $controller->checkAccess(
            'view',
            new PreviewContractVerse(['allowView' => true])
        );

        $this->expectException(ForbiddenHttpException::class);
        $controller->checkAccess(
            'view',
            new PreviewContractVerse(['allowView' => false])
        );
    }

    public function testProductionViewActionUsesRealPrivateSceneVisibility(): void
    {
        $this->withPreviewDatabase(function (Connection $db): void {
            $db->createCommand()->insert('verse', [
                'id' => 10,
                'author_id' => 42,
                'name' => 'Private owner scene',
                'info' => '',
                'data' => '{}',
                'created_at' => 100,
                'updated_at' => 100,
            ])->execute();

            $controller = $this->createController();
            $viewConfig = $controller->actions()['view'];
            $this->assertSame([$controller, 'checkAccess'], $viewConfig['checkAccess']);

            $this->switchPreviewIdentity(42);
            $ownerAction = Yii::createObject($viewConfig, ['view', $controller]);
            $ownerScene = $ownerAction->run(10);
            $this->assertSame(10, (int) $ownerScene->id);

            $this->switchPreviewIdentity(84);
            $otherAction = Yii::createObject($viewConfig, ['view', $controller]);
            $this->expectException(ForbiddenHttpException::class);
            $otherAction->run(10);
        });
    }

    private function createController(): PreviewContractVerseController
    {
        return new PreviewContractVerseController('verse', Yii::$app);
    }

    private function withPreviewDatabase(callable $callback): void
    {
        $originalDb = Yii::$app->get('db');
        $originalIdentity = Yii::$app->user->identity;
        $originalQueryParams = Yii::$app->request->getQueryParams();
        $db = new Connection(['dsn' => 'sqlite::memory:']);
        $db->open();
        Yii::$app->set('db', $db);
        $this->resetVerseRequestCaches();

        try {
            $db->createCommand(<<<'SQL'
CREATE TABLE verse (
    id INTEGER PRIMARY KEY,
    author_id INTEGER NOT NULL,
    updater_id INTEGER NULL,
    image_id INTEGER NULL,
    name TEXT NOT NULL,
    info TEXT NULL,
    data TEXT NULL,
    description TEXT NULL,
    created_at INTEGER NULL,
    updated_at INTEGER NULL
)
SQL)->execute();
            $db->createCommand('CREATE TABLE property (id INTEGER PRIMARY KEY, key TEXT NOT NULL)')->execute();
            $db->createCommand('CREATE TABLE verse_property (verse_id INTEGER NOT NULL, property_id INTEGER NOT NULL)')->execute();
            $db->createCommand('CREATE TABLE group_verse (group_id INTEGER NOT NULL, verse_id INTEGER NOT NULL)')->execute();
            $db->createCommand('CREATE TABLE group_user (group_id INTEGER NOT NULL, user_id INTEGER NOT NULL)')->execute();
            $callback($db);
        } finally {
            Yii::$app->request->setQueryParams($originalQueryParams);
            Yii::$app->user->switchIdentity($originalIdentity);
            Yii::$app->set('db', $originalDb);
            $this->resetVerseRequestCaches();
            $db->close();
        }
    }

    private function resetVerseRequestCaches(): void
    {
        $reflection = new \ReflectionClass(Verse::class);
        foreach ([
            'groupEditableVerseIdSetByUser',
            'groupEditableMemoByUser',
            'groupEditableCheckCountByUser',
        ] as $propertyName) {
            $property = $reflection->getProperty($propertyName);
            $property->setValue(null, []);
        }
    }

    private function switchPreviewIdentity(int $id): void
    {
        Yii::$app->user->switchIdentity(new PreviewContractIdentity($id));
    }
}

final class PreviewContractIdentity implements IdentityInterface
{
    public function __construct(private readonly int $id)
    {
    }

    public static function findIdentity($id): ?self
    {
        return new self((int) $id);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?self
    {
        return null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAuthKey(): string
    {
        return '';
    }

    public function validateAuthKey($authKey): bool
    {
        return false;
    }
}

final class PreviewContractVerseController extends VerseController
{
    public function applyCurrentUserFilterForTest(
        ActiveQuery $query,
        int $userId
    ): void {
        $this->applyCurrentUserFilter($query, $userId);
    }
}

final class PreviewContractVerse extends Verse
{
    public bool $allowView = false;

    public function attributes(): array
    {
        return [];
    }

    public function getViewable(): bool
    {
        return $this->allowView;
    }
}
