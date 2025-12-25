<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\Property;
use api\modules\v1\models\Snapshot;
use api\modules\v1\models\Verse;
use api\modules\v1\models\VerseProperty;
use api\modules\v1\models\VerseSearch;
use api\modules\v1\models\data\VerseCodeTool;
use bizley\jwt\JwtHttpBearerAuth;
use mdm\admin\components\AccessControl;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use api\modules\v1\models\Tags;
use api\modules\v1\models\VerseTags;
use yii\base\Exception;
class VerseController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\Verse';
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => [
                    'X-Pagination-Total-Count',
                    'X-Pagination-Page-Count',
                    'X-Pagination-Current-Page',
                    'X-Pagination-Per-Page',
                ],
            ],
        ];

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
            'except' => ['options'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
        ];

        return $behaviors;
    }
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }
    public function actionPublic()
    {
        $searchModel = new VerseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);


        $dataProvider->query->innerJoin('verse_property AS vp1', 'vp1.verse_id = verse.id')
            ->innerJoin('property', 'property.id = vp1.property_id')
            ->andWhere(['property.key' => 'public']);
            /*
        // 添加 public 标签条件
        $dataProvider->query->innerJoin('verse_tags AS vt_public', 'vt_public.verse_id = verse.id')
            ->innerJoin('tags AS t_public', 't_public.id = vt_public.tags_id')
            ->andWhere(['t_public.key' => 'public']);*/

        // 处理额外的标签过滤
        $this->applyTagsFilter($dataProvider->query, 'vt_extra');

        return $dataProvider;
    }
   
    public function actionIndex()
    {
        $searchModel = new VerseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['author_id' => Yii::$app->user->id]);

        // 处理标签过滤
        $this->applyTagsFilter($dataProvider->query);

        return $dataProvider;
    }

    public function actionUpdateCode($id)
    {
        $post = Yii::$app->request->post();
        $model = new VerseCodeTool($id);
        $model->load($post, '');
        if ($model->validate()) {
            $model->save();
        } else {
            throw new BadRequestHttpException(json_encode($model->errors));
        }
        return $model;
    }

    /**
     * 为 verse 添加 public 属性
     * POST /verse/{id}/add-public
     * @param int $id verse ID
     * @return array
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     * @throws BadRequestHttpException
     */
    public function actionAddPublic($id)
    {
        $verse = Verse::findOne($id);
        if (!$verse) {
            throw new NotFoundHttpException('Verse not found');
        }

        // 验证当前用户是否为作者
        if ($verse->author_id !== Yii::$app->user->id) {
            throw new ForbiddenHttpException('You are not authorized to modify this verse');
        }

        // 获取或创建 public 属性
        $property = Property::findOne(['key' => 'public']);
        if (!$property) {
            $property = new Property();
            $property->key = 'public';
            $property->info = 'Public visibility';
            if (!$property->save()) {
                throw new BadRequestHttpException(json_encode($property->errors));
            }
        }

        // 检查是否已存在关联
        $existing = VerseProperty::findOne([
            'verse_id' => $id,
            'property_id' => $property->id,
        ]);

        if ($existing) {
            return ['success' => true, 'message' => 'Already public'];
        }

        // 创建关联
        $verseProperty = new VerseProperty();
        $verseProperty->verse_id = $id;
        $verseProperty->property_id = $property->id;

        if (!$verseProperty->save()) {
            throw new BadRequestHttpException(json_encode($verseProperty->errors));
        }

        return ['success' => true, 'message' => 'Public property added'];
    }

    /**
     * 删除 verse 的 public 属性
     * POST /verse/remove-public?id=xxx
     * @param int $id verse ID
     * @return array
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionRemovePublic($id)
    {
        $verse = Verse::findOne($id);
        if (!$verse) {
            throw new NotFoundHttpException('Verse not found');
        }

        // 验证当前用户是否为作者
        if ($verse->author_id !== Yii::$app->user->id) {
            throw new ForbiddenHttpException('You are not authorized to modify this verse');
        }

        // 获取 public 属性
        $property = Property::findOne(['key' => 'public']);
        if (!$property) {
            return ['success' => true, 'message' => 'No public property exists'];
        }

        // 删除关联
        $deleted = VerseProperty::deleteAll([
            'verse_id' => $id,
            'property_id' => $property->id,
        ]);

        if ($deleted > 0) {
            return ['success' => true, 'message' => 'Public property removed'];
        }

        return ['success' => true, 'message' => 'Verse was not public'];
    }
    /**
     * 为 verse 添加标签
     * POST /verse/{id}/tag?tags_id={tags_id}
     * @param int $id verse ID
     * @param int $tags_id 标签 ID
     * @return array
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionAddTag($id, $tags_id)
    {
        $verse = Verse::findOne($id);
        if (!$verse) {
            throw new NotFoundHttpException('Verse not found');
        }

        $tags = Tags::findOne($tags_id);
        if (!$tags) {
            throw new Exception('Tags not found');
        }

        $model = new VerseTags();
        $model->verse_id = $id;
        $model->tags_id = $tags_id;

        if ($model->save()) {
            return [
                'success' => true,
                'message' => 'Tag added'
            ];
        } else {
            throw new Exception(json_encode($model->getErrors()));
        }
    }

    /**
     * 移除 verse 的标签
     * DELETE /verse/{id}/tag?tags_id={tags_id}
     * @param int $id verse ID
     * @param int $tags_id 标签 ID
     * @return array
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionRemoveTag($id, $tags_id)
    {
        $verse = Verse::findOne($id);
        if (!$verse) {
            throw new NotFoundHttpException('Verse not found');
        }

        $model = VerseTags::find()->where(['verse_id' => $id, 'tags_id' => $tags_id])->one();
        if ($model) {
            $model->delete();
            return [
                'success' => true,
                'message' => 'Tag removed'
            ];
        }
        throw new Exception('VerseTags not found');
    }

    /**
     * 为 verse 创建快照
     * POST /verse/{id}/take-photo
     * @param int $id verse ID
     * @return array
     * @throws Exception
     */
    public function actionTakePhoto($id)
    {
        $snapshot = Snapshot::CreateById($id);
        if ($snapshot->validate()) {
            $snapshot->save();
        } else {
            throw new Exception(json_encode($snapshot->errors), 400);
        }
        return $snapshot->toArray([], ['code', 'id', 'name', 'data', 'description', 'metas', 'resources', 'uuid', 'image']);
    }

    /**
     * 应用标签过滤到查询
     * @param \yii\db\ActiveQuery $query 查询对象
     * @param string $alias 表别名，默认为 'verse_tags'
     */
    private function applyTagsFilter($query, $alias = 'verse_tags')
    {
        $tags = Yii::$app->request->get('tags');
        if ($tags) {
            $tagsArray = array_map('intval', explode(',', $tags));
            if (!empty($tagsArray)) {
                $query->innerJoin("verse_tags AS {$alias}", "{$alias}.verse_id = verse.id")
                    ->andWhere(['in', "{$alias}.tags_id", $tagsArray])
                    ->groupBy('verse.id');
            }
        }
    }
}
