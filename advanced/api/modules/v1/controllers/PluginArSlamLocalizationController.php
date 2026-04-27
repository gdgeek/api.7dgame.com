<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Verse;
use bizley\jwt\JwtHttpBearerAuth;
use mdm\admin\components\AccessControl;
use Yii;
use yii\db\Query;
use yii\filters\auth\CompositeAuth;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class PluginArSlamLocalizationController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                [
                    'class' => JwtHttpBearerAuth::class,
                    'throwException' => false,
                ],
            ],
            'except' => ['options'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
        ];

        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'bindings' => ['GET'],
            'create-bindings' => ['POST'],
            'delete-binding' => ['DELETE'],
        ];
    }

    public function actionBindings()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $verseIds = $this->parseIds(Yii::$app->request->get('verseIds', ''));
        if (!$verseIds) {
            return [];
        }

        $userId = (int) Yii::$app->user->id;
        $rows = (new Query())
            ->select([
                'verseId' => 'vs.verse_id',
                'spaceId' => 's.id',
                'spaceName' => 's.name',
            ])
            ->from(['vs' => '{{%verse_space}}'])
            ->innerJoin(['s' => '{{%space}}'], 's.id = vs.space_id')
            ->where(['vs.verse_id' => $verseIds, 's.user_id' => $userId])
            ->all();

        return array_map(static fn (array $row): array => [
            'verseId' => (int) $row['verseId'],
            'spaceId' => (int) $row['spaceId'],
            'spaceName' => (string) $row['spaceName'],
        ], $rows);
    }

    public function actionCreateBindings()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $body = Yii::$app->request->bodyParams;
        $verseIds = $this->parseIds($body['verseIds'] ?? []);
        $spaceId = (int)($body['spaceId'] ?? 0);
        if ($spaceId <= 0 || !$verseIds) {
            throw new BadRequestHttpException('spaceId and verseIds are required.');
        }

        $space = (new Query())
            ->select(['id'])
            ->from('{{%space}}')
            ->where(['id' => $spaceId, 'user_id' => (int) Yii::$app->user->id])
            ->one();
        if (!$space) {
            throw new ForbiddenHttpException('You do not have permission to bind this space.');
        }

        $this->assertEditableVerses($verseIds);

        $now = gmdate('Y-m-d H:i:s');
        $db = Yii::$app->db;

        $transaction = $db->beginTransaction();
        try {
            $existingRows = (new Query())
                ->select(['verse_id', 'space_id'])
                ->from('{{%verse_space}}')
                ->where(['verse_id' => $verseIds])
                ->all($db);

            foreach ($existingRows as $row) {
                if ((int) $row['space_id'] !== $spaceId) {
                    throw new ConflictHttpException('Verse is already bound to another space.');
                }
            }

            foreach ($verseIds as $verseId) {
                $db->createCommand()->upsert('{{%verse_space}}', [
                    'verse_id' => $verseId,
                    'space_id' => $spaceId,
                    'created_at' => $now,
                ], [
                    'space_id' => $spaceId,
                ])->execute();
            }

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw $exception;
        }

        return [
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'spaceId' => $spaceId,
                'verseIds' => $verseIds,
            ],
        ];
    }

    public function actionDeleteBinding(int $verseId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($verseId <= 0) {
            throw new BadRequestHttpException('verseId is required.');
        }

        $row = (new Query())
            ->select([
                'id' => 'vs.id',
                'spaceUserId' => 's.user_id',
            ])
            ->from(['vs' => '{{%verse_space}}'])
            ->innerJoin(['s' => '{{%space}}'], 's.id = vs.space_id')
            ->where(['vs.verse_id' => $verseId])
            ->one();

        if (!$row) {
            return [
                'code' => 0,
                'message' => 'ok',
                'data' => [
                    'verseId' => $verseId,
                ],
            ];
        }

        if ((int) $row['spaceUserId'] !== (int) Yii::$app->user->id) {
            throw new ForbiddenHttpException('You do not have permission to unbind this verse.');
        }

        Yii::$app->db->createCommand()
            ->delete('{{%verse_space}}', ['id' => (int) $row['id']])
            ->execute();

        return [
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'verseId' => $verseId,
            ],
        ];
    }

    private function assertEditableVerses(array $verseIds): void
    {
        $verses = Verse::find()->where(['id' => $verseIds])->all();
        if (count($verses) !== count($verseIds)) {
            throw new BadRequestHttpException('Verse not found.');
        }

        foreach ($verses as $verse) {
            if (!$verse->editable) {
                throw new ForbiddenHttpException('You do not have permission to bind this verse.');
            }
        }
    }

    private function parseIds($raw): array
    {
        $items = is_array($raw) ? $raw : explode(',', (string)$raw);
        $ids = [];
        foreach ($items as $item) {
            $id = (int) trim((string)$item);
            if ($id > 0) {
                $ids[$id] = true;
            }
        }
        return array_keys($ids);
    }
}
