<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\PluginCampusController;
use api\modules\v1\models\File;
use api\modules\v1\models\Meta;
use api\modules\v1\models\MetaResource;
use api\modules\v1\models\Organization;
use api\modules\v1\models\Resource;
use api\modules\v1\models\User;
use api\modules\v1\models\UserInfo;
use api\modules\v1\models\UserOrganization;
use api\modules\v1\models\Verse;
use api\modules\v1\models\VerseMeta;
use api\modules\v1\services\ScenePackageService;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Component;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\web\User as WebUser;

final class PluginCampusControllerTest extends TestCase
{
    private const SAFE_PASSWORD = 'N9#CampusSafe';
    private const NEW_PASSWORD = 'N9#CampusReset';
    private const USERNAMES = [
        'campus-root-operator',
        'campus-admin-operator',
        'campus-manager-operator',
        'campus-target-manager',
        'campus-target-user',
        'campus-peer-admin',
        'campus-root-target',
        'campus-outside-user',
    ];
    private const ORGANIZATION_NAMES = [
        'campus-org-north',
        'campus-org-south',
    ];

    private $originalUserComponent;
    private $originalAuthManager;
    private $originalRequestComponent;
    private $originalResponseComponent;
    private string $fixtureSuffix = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureSuffix = '-' . bin2hex(random_bytes(4));
        $this->originalUserComponent = Yii::$app->get('user', false);
        $this->originalAuthManager = Yii::$app->get('authManager', false);
        $this->originalRequestComponent = Yii::$app->get('request', false);
        $this->originalResponseComponent = Yii::$app->get('response', false);
        $this->cleanupFixtures();
    }

    protected function tearDown(): void
    {
        Yii::$app->set('user', $this->originalUserComponent);
        Yii::$app->set('authManager', $this->originalAuthManager);
        Yii::$app->set('request', $this->originalRequestComponent);
        Yii::$app->set('response', $this->originalResponseComponent);
        $this->cleanupFixtures();
        parent::tearDown();
    }

    public function testPublishedApiConfigRegistersCampusRoutes(): void
    {
        $config = require dirname(__DIR__, 4) . '/files/api/config/main.php';
        $rules = $config['components']['urlManager']['rules'] ?? [];
        $campusRule = null;

        foreach ($rules as $rule) {
            if (is_array($rule) && ($rule['controller'] ?? null) === 'v1/plugin-campus') {
                $campusRule = $rule;
                break;
            }
        }

        $this->assertNotNull($campusRule);
        $this->assertSame(
            ['users', 'password', 'clear-content-preview', 'clear-content', 'import-scene-zip', 'upload-resource'],
            $campusRule['only'] ?? null
        );
        $this->assertSame(
            [
                'GET users' => 'users',
                'POST users/password' => 'password',
                'POST users/clear-content-preview' => 'clear-content-preview',
                'POST users/clear-content' => 'clear-content',
                'POST users/import-scene-zip' => 'import-scene-zip',
                'POST users/upload-resource' => 'upload-resource',
            ],
            $campusRule['extraPatterns'] ?? null
        );
    }

    public function testAdminListsOnlySameOrganizationManagersAndUsers(): void
    {
        [$north, $south] = $this->createOrganizations();
        $admin = $this->createUser('campus-admin-operator');
        $manager = $this->createUser('campus-target-manager');
        $student = $this->createUser('campus-target-user');
        $peerAdmin = $this->createUser('campus-peer-admin');
        $rootTarget = $this->createUser('campus-root-target');
        $outside = $this->createUser('campus-outside-user');

        foreach ([$admin, $manager, $student, $peerAdmin, $rootTarget] as $user) {
            $this->bindOrganization((int)$user->id, (int)$north->id);
        }
        $this->bindOrganization((int)$outside->id, (int)$south->id);

        $this->bootActor($admin, [
            $admin->id => ['user', 'admin'],
            $manager->id => ['user', 'manager'],
            $student->id => ['user'],
            $peerAdmin->id => ['user', 'admin'],
            $rootTarget->id => ['user', 'root'],
            $outside->id => ['user', 'manager'],
        ], ['organization_id' => (string)$north->id]);

        $result = $this->controller()->actionUsers();
        $usernames = array_column($result['data'], 'username');

        $this->assertSame(0, $result['code']);
        $this->assertContains($manager->username, $usernames);
        $this->assertContains($student->username, $usernames);
        $this->assertNotContains($peerAdmin->username, $usernames);
        $this->assertNotContains($rootTarget->username, $usernames);
        $this->assertNotContains($outside->username, $usernames);
    }

    public function testAdminCanUpdateSameOrganizationManagerPasswordOnly(): void
    {
        [$north] = $this->createOrganizations();
        $admin = $this->createUser('campus-admin-operator');
        $manager = $this->createUser('campus-target-manager');
        $this->bindOrganization((int)$admin->id, (int)$north->id);
        $this->bindOrganization((int)$manager->id, (int)$north->id);

        $this->bootActor($admin, [
            $admin->id => ['user', 'admin'],
            $manager->id => ['user', 'manager'],
        ], [], [
            'organization_id' => (int)$north->id,
            'user_ids' => [(int)$manager->id],
            'password' => self::NEW_PASSWORD,
        ]);

        $result = $this->controller()->actionPassword();
        $manager->refresh();

        $this->assertSame(0, $result['code']);
        $this->assertSame(1, $result['data']['success_count']);
        $this->assertTrue(Yii::$app->security->validatePassword(self::NEW_PASSWORD, $manager->password_hash));
    }

    public function testAdminCannotUpdatePeerAdminOrOutsideOrganizationUser(): void
    {
        [$north, $south] = $this->createOrganizations();
        $admin = $this->createUser('campus-admin-operator');
        $peerAdmin = $this->createUser('campus-peer-admin');
        $outside = $this->createUser('campus-outside-user');

        $this->bindOrganization((int)$admin->id, (int)$north->id);
        $this->bindOrganization((int)$peerAdmin->id, (int)$north->id);
        $this->bindOrganization((int)$outside->id, (int)$south->id);

        $roleMap = [
            $admin->id => ['user', 'admin'],
            $peerAdmin->id => ['user', 'admin'],
            $outside->id => ['user'],
        ];

        $this->bootActor($admin, $roleMap, [], [
            'organization_id' => (int)$north->id,
            'user_ids' => [(int)$peerAdmin->id],
            'password' => self::NEW_PASSWORD,
        ]);
        $peerAdminResult = $this->controller()->actionPassword();
        $this->assertSame(403, Yii::$app->response->statusCode);
        $this->assertSame(2003, $peerAdminResult['code']);

        Yii::$app->set('response', new Response());
        Yii::$app->set('request', new PluginCampusTestRequest([], [
            'organization_id' => (int)$north->id,
            'user_ids' => [(int)$outside->id],
            'password' => self::NEW_PASSWORD,
        ]));
        $outsideResult = $this->controller()->actionPassword();

        $this->assertSame(403, Yii::$app->response->statusCode);
        $this->assertSame(2003, $outsideResult['code']);
    }

    public function testManagerListsOnlySameOrganizationUsers(): void
    {
        [$north] = $this->createOrganizations();
        $manager = $this->createUser('campus-manager-operator');
        $student = $this->createUser('campus-target-user');
        $this->bindOrganization((int)$manager->id, (int)$north->id);
        $this->bindOrganization((int)$student->id, (int)$north->id);

        $this->bootActor($manager, [
            $manager->id => ['user', 'manager'],
            $student->id => ['user'],
        ], ['organization_id' => (string)$north->id]);

        $result = $this->controller()->actionUsers();
        $usernames = array_column($result['data'], 'username');

        $this->assertSame(0, $result['code']);
        $this->assertContains($student->username, $usernames);
        $this->assertNotContains($manager->username, $usernames);
    }

    public function testCampusUserListRequiresOrganizationScope(): void
    {
        $root = $this->createUser('campus-root-operator');

        $this->bootActor($root, [
            $root->id => ['user', 'root'],
        ]);

        $result = $this->controller()->actionUsers();

        $this->assertSame(422, Yii::$app->response->statusCode);
        $this->assertSame(4220, $result['code']);
    }

    public function testClearPreviewAndClearContentUseSameResourceAndVerseScope(): void
    {
        [$north] = $this->createOrganizations();
        $admin = $this->createUser('campus-admin-operator');
        $student = $this->createUser('campus-target-user');

        $this->bindOrganization((int)$admin->id, (int)$north->id);
        $this->bindOrganization((int)$student->id, (int)$north->id);
        $this->createUserContent($student);

        $roleMap = [
            $admin->id => ['user', 'admin'],
            $student->id => ['user'],
        ];

        $body = [
            'organization_id' => (int)$north->id,
            'user_ids' => [(int)$student->id],
        ];
        $this->bootActor($admin, $roleMap, [], $body);
        $preview = $this->controller()->actionClearContentPreview();

        $this->assertSame(1, $preview['data']['user_count']);
        $this->assertSame(1, $preview['data']['verse_count']);
        $this->assertSame(1, $preview['data']['resource_count']);

        Yii::$app->set('response', new Response());
        Yii::$app->set('request', new PluginCampusTestRequest([], $body + ['confirm' => true]));
        $clearResult = $this->controller()->actionClearContent();

        $this->assertSame(0, $clearResult['code']);
        $this->assertSame(1, $clearResult['data']['success_count']);
        $this->assertSame(0, (int)Verse::find()->where(['author_id' => $student->id])->count());
        $this->assertSame(0, (int)Resource::find()->where(['author_id' => $student->id])->count());
    }

    public function testRootCanManageOrganizationAdminButNotRootTargets(): void
    {
        [$north] = $this->createOrganizations();
        $root = $this->createUser('campus-root-operator');
        $admin = $this->createUser('campus-peer-admin');
        $rootTarget = $this->createUser('campus-root-target');

        foreach ([$root, $admin, $rootTarget] as $user) {
            $this->bindOrganization((int)$user->id, (int)$north->id);
        }

        $roleMap = [
            $root->id => ['user', 'root'],
            $admin->id => ['user', 'admin'],
            $rootTarget->id => ['user', 'root'],
        ];

        $this->bootActor($root, $roleMap, [], [
            'organization_id' => (int)$north->id,
            'user_ids' => [(int)$admin->id],
            'password' => self::NEW_PASSWORD,
        ]);
        $adminResult = $this->controller()->actionPassword();

        $this->assertSame(0, $adminResult['code']);
        $this->assertSame(1, $adminResult['data']['success_count']);

        Yii::$app->set('response', new Response());
        Yii::$app->set('request', new PluginCampusTestRequest([], [
            'organization_id' => (int)$north->id,
            'user_ids' => [(int)$rootTarget->id],
            'password' => self::NEW_PASSWORD,
        ]));
        $rootTargetResult = $this->controller()->actionPassword();

        $this->assertSame(403, Yii::$app->response->statusCode);
        $this->assertSame(2003, $rootTargetResult['code']);
    }

    public function testSceneImportServiceCanCreateVerseForTargetUser(): void
    {
        [$north] = $this->createOrganizations();
        $admin = $this->createUser('campus-admin-operator');
        $student = $this->createUser('campus-target-user');
        $this->bindOrganization((int)$admin->id, (int)$north->id);
        $this->bindOrganization((int)$student->id, (int)$north->id);

        $this->bootActor($admin, [
            $admin->id => ['user', 'admin'],
            $student->id => ['user'],
        ]);

        $service = new ScenePackageService();
        $result = $service->importScene([
            'verse' => [
                'name' => 'Campus Imported Verse',
                'description' => 'target ownership test',
                'data' => null,
                'version' => 1,
                'uuid' => 'campus-import-original-verse',
            ],
            'metas' => [],
            'resourceFileMappings' => [],
        ], (int)$student->id);

        $verse = Verse::findOne($result['verseId']);

        $this->assertNotNull($verse);
        $this->assertSame((int)$student->id, (int)$verse->author_id);
        $this->assertSame((int)$admin->id, (int)Yii::$app->user->id);
    }

    public function testCampusResourceCreationUsesTargetUserOwnership(): void
    {
        [$north] = $this->createOrganizations();
        $admin = $this->createUser('campus-admin-operator');
        $student = $this->createUser('campus-target-user');
        $this->bindOrganization((int)$admin->id, (int)$north->id);
        $this->bindOrganization((int)$student->id, (int)$north->id);

        $this->bootActor($admin, [
            $admin->id => ['user', 'admin'],
            $student->id => ['user'],
        ]);

        $controller = $this->controller();
        $method = new \ReflectionMethod($controller, 'createCampusResourceForUser');
        $created = $method->invoke($controller, $student, [
            'url' => 'https://example.com/campus-upload.glb',
            'filename' => 'campus-upload.glb',
            'key' => 'campus/resources/test/campus-upload.glb',
            'md5' => 'd41d8cd98f00b204e9800998ecf8427e',
            'mime_type' => 'model/gltf-binary',
            'size' => 128,
            'resource_name' => 'Campus Uploaded Resource',
            'resource_type' => 'polygen',
            'info' => '{"source":"test"}',
        ]);

        $file = File::findOne($created['file_id']);
        $resource = Resource::findOne($created['resource_id']);
        $createdAgain = $method->invoke($controller, $student, [
            'url' => 'https://example.com/campus-upload.glb',
            'filename' => 'campus-upload.glb',
            'key' => 'campus/resources/test/campus-upload.glb',
            'md5' => 'd41d8cd98f00b204e9800998ecf8427e',
            'mime_type' => 'model/gltf-binary',
            'size' => 128,
            'resource_name' => 'Campus Uploaded Resource Copy',
            'resource_type' => 'polygen',
            'info' => '{"source":"test"}',
        ]);

        $this->assertNotNull($file);
        $this->assertNotNull($resource);
        $this->assertSame((int)$student->id, (int)$file->user_id);
        $this->assertSame((int)$student->id, (int)$resource->author_id);
        $this->assertSame((int)$student->id, (int)$resource->updater_id);
        $this->assertSame('polygen', $resource->type);
        $this->assertSame($created['file_id'], $createdAgain['file_id']);
        $this->assertNotSame($created['resource_id'], $createdAgain['resource_id']);
        $this->assertSame((int)$admin->id, (int)Yii::$app->user->id);
    }

    public function testCampusResourceUploadPolicyAllowsExpectedResourceTypes(): void
    {
        $controller = $this->controller();
        $method = new \ReflectionMethod($controller, 'assertResourceUploadPolicy');

        $allowed = [
            ['lesson.glb', 'polygen', 'model/gltf-binary'],
            ['voxel.vox', 'voxel', 'application/octet-stream'],
            ['cover.png', 'picture', 'image/png'],
            ['intro.mp4', 'video', 'video/mp4'],
            ['voice.mp3', 'audio', 'audio/mpeg'],
            ['effect.json', 'particle', 'application/json'],
            ['handout.pdf', 'file', 'application/pdf'],
        ];

        foreach ($allowed as [$filename, $resourceType, $mimeType]) {
            $method->invoke($controller, $filename, $resourceType, $this->uploadedFile($filename, $mimeType));
            $this->addToAssertionCount(1);
        }
    }

    public function testCampusResourceUploadPolicyRejectsScriptablePublicFiles(): void
    {
        $controller = $this->controller();
        $method = new \ReflectionMethod($controller, 'assertResourceUploadPolicy');

        $this->expectException(BadRequestHttpException::class);
        $method->invoke($controller, 'lesson.html', 'file', $this->uploadedFile('lesson.html', 'text/html'));
    }

    public function testCampusResourceUploadPolicyRejectsDoubleExtensions(): void
    {
        $controller = $this->controller();
        $method = new \ReflectionMethod($controller, 'assertResourceUploadPolicy');

        $this->expectException(BadRequestHttpException::class);
        $method->invoke($controller, 'lesson.php.glb', 'polygen', $this->uploadedFile('lesson.php.glb', 'model/gltf-binary'));
    }

    public function testCampusResourceUploadPolicyRejectsMismatchedTypeAndExtension(): void
    {
        $controller = $this->controller();
        $method = new \ReflectionMethod($controller, 'assertResourceUploadPolicy');

        $this->expectException(BadRequestHttpException::class);
        $method->invoke($controller, 'movie.mp4', 'picture', $this->uploadedFile('movie.mp4', 'video/mp4'));
    }

    public function testCampusResourceUploadPolicyRejectsOversizedFiles(): void
    {
        $controller = $this->controller();
        $method = new \ReflectionMethod($controller, 'assertResourceUploadPolicy');

        $this->expectException(BadRequestHttpException::class);
        $method->invoke($controller, 'lesson.glb', 'polygen', $this->uploadedFile('lesson.glb', 'model/gltf-binary', 209715201));
    }

    public function testCampusResourceInfoRejectsOversizedPayloads(): void
    {
        $controller = $this->controller();
        $method = new \ReflectionMethod($controller, 'normalizeResourceInfo');

        $this->expectException(BadRequestHttpException::class);
        $method->invoke($controller, str_repeat('a', 16385));
    }

    private function controller(): PluginCampusController
    {
        return new PluginCampusController('plugin-campus', Yii::$app->getModule('v1'));
    }

    private function uploadedFile(string $name, string $type, int $size = 1024): UploadedFile
    {
        $file = new UploadedFile();
        $file->name = $name;
        $file->type = $type;
        $file->size = $size;
        $file->tempName = '';
        $file->error = UPLOAD_ERR_OK;

        return $file;
    }

    /**
     * @return Organization[]
     */
    private function createOrganizations(): array
    {
        $organizations = [];
        foreach (self::ORGANIZATION_NAMES as $baseName) {
            $name = $this->fixtureOrganizationName($baseName);
            $organization = new Organization([
                'title' => $name,
                'name' => $name,
            ]);
            if (!$organization->save(false) || $organization->id === null) {
                throw new \RuntimeException('Failed to create campus organization fixture: ' . $name);
            }
            $organizations[] = $organization;
        }

        return $organizations;
    }

    private function createUser(string $username): User
    {
        $username = $this->fixtureUsername($username);
        $user = new User();
        $user->username = $username;
        $user->nickname = $username;
        $user->email = $username . '@example.com';
        $user->auth_key = Yii::$app->security->generateRandomString();
        $user->password_hash = Yii::$app->security->generatePasswordHash(self::SAFE_PASSWORD);
        $user->status = 10;
        $user->created_at = time();
        $user->updated_at = time();
        if (!$user->save(false) || $user->id === null) {
            throw new \RuntimeException('Failed to create campus user fixture: ' . $username);
        }

        return $user;
    }

    private function fixtureUsername(string $baseName): string
    {
        return $baseName . $this->fixtureSuffix;
    }

    private function fixtureOrganizationName(string $baseName): string
    {
        return $baseName . $this->fixtureSuffix;
    }

    private function bindOrganization(int $userId, int $organizationId): void
    {
        $binding = new UserOrganization([
            'user_id' => $userId,
            'organization_id' => $organizationId,
        ]);
        $binding->save(false);
    }

    private function createUserContent(User $user): void
    {
        if ($user->id === null) {
            throw new \RuntimeException('Campus user fixture must be saved before content fixtures are created.');
        }
        $previousIdentity = Yii::$app->user?->identity ?? null;
        if (Yii::$app->has('user')) {
            Yii::$app->user->setIdentity($user);
        }

        $file = new File([
            'url' => 'https://example.com/campus-test.glb',
            'filename' => 'campus-test.glb',
            'key' => 'campus-test.glb',
            'user_id' => $user->id,
        ]);
        $file->save(false);

        $resource = new Resource([
            'name' => 'Campus Test Resource',
            'type' => 'polygen',
            'uuid' => 'campus-test-resource-' . $user->id,
            'file_id' => $file->id,
            'author_id' => $user->id,
            'updater_id' => $user->id,
        ]);
        $resource->save(false);

        $verse = new Verse([
            'name' => 'Campus Test Verse',
            'uuid' => 'campus-test-verse-' . $user->id,
            'author_id' => $user->id,
            'updater_id' => $user->id,
        ]);
        $verse->save(false);

        $meta = new Meta([
            'title' => 'Campus Test Meta',
            'uuid' => 'campus-test-meta-' . $user->id,
            'author_id' => $user->id,
            'updater_id' => $user->id,
        ]);
        $meta->save(false);

        (new VerseMeta(['verse_id' => $verse->id, 'meta_id' => $meta->id]))->save(false);
        (new MetaResource(['meta_id' => $meta->id, 'resource_id' => $resource->id]))->save(false);

        if (Yii::$app->has('user')) {
            Yii::$app->user->setIdentity($previousIdentity);
        }
    }

    private function bootActor(User $actor, array $roleMap, array $queryParams = [], array $bodyParams = []): void
    {
        $webUser = new WebUser([
            'identityClass' => User::class,
            'enableSession' => false,
        ]);
        $webUser->setIdentity($actor);

        Yii::$app->set('user', $webUser);
        Yii::$app->set('authManager', new PluginCampusRoleMapAuthManager($roleMap));
        Yii::$app->set('request', new PluginCampusTestRequest($queryParams, $bodyParams));
        Yii::$app->set('response', new Response());
    }

    private function cleanupFixtures(): void
    {
        $userConditions = ['or'];
        foreach (self::USERNAMES as $username) {
            $userConditions[] = ['username' => $username];
            $userConditions[] = ['like', 'username', $username . '-%', false];
        }

        $userIds = User::find()
            ->select('id')
            ->where($userConditions)
            ->column();

        if (!empty($userIds)) {
            $verseIds = Verse::find()->select('id')->where(['author_id' => $userIds])->column();
            $metaIds = Meta::find()->select('id')->where(['author_id' => $userIds])->column();
            $resourceIds = Resource::find()->select('id')->where(['author_id' => $userIds])->column();

            if (!empty($verseIds)) {
                VerseMeta::deleteAll(['verse_id' => $verseIds]);
                Verse::deleteAll(['id' => $verseIds]);
            }
            if (!empty($metaIds)) {
                MetaResource::deleteAll(['meta_id' => $metaIds]);
                Meta::deleteAll(['id' => $metaIds]);
            }
            if (!empty($resourceIds)) {
                MetaResource::deleteAll(['resource_id' => $resourceIds]);
                Resource::deleteAll(['id' => $resourceIds]);
            }

            File::deleteAll(['user_id' => $userIds]);
            UserOrganization::deleteAll(['user_id' => $userIds]);
            UserInfo::deleteAll(['user_id' => $userIds]);
            User::deleteAll(['id' => $userIds]);
        }

        $organizationConditions = ['or'];
        foreach (self::ORGANIZATION_NAMES as $name) {
            $organizationConditions[] = ['name' => $name];
            $organizationConditions[] = ['like', 'name', $name . '-%', false];
        }

        Organization::deleteAll($organizationConditions);
    }
}

final class PluginCampusRoleMapAuthManager extends Component
{
    public function __construct(private array $roleMap, $config = [])
    {
        parent::__construct($config);
    }

    public function getRolesByUser($userId): array
    {
        $roles = $this->roleMap[(int)$userId] ?? [];
        $map = [];
        foreach ($roles as $role) {
            $map[$role] = (object)['name' => $role];
        }

        return $map;
    }
}

final class PluginCampusTestRequest extends \yii\web\Request
{
    public function __construct(
        private array $queryParams,
        private array $bodyParams,
        $config = []
    ) {
        parent::__construct($config);
    }

    public function get($name = null, $defaultValue = null)
    {
        if ($name === null) {
            return $this->queryParams;
        }

        return $this->queryParams[$name] ?? $defaultValue;
    }

    public function getBodyParam($name, $defaultValue = null)
    {
        return $this->bodyParams[$name] ?? $defaultValue;
    }

    public function getBodyParams()
    {
        return $this->bodyParams;
    }
}
