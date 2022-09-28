<?php
namespace api\modules\v1\models;

use api\modules\v1\models\User;
use api\modules\v1\models\Resource;
use Yii;
use yii\base\Model;

/**
 * Signup form
 */
class UserCreation extends Model
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

        ];
    }
    public function extraFields()
    {
        return ['pictureCount', 'polygenCount', 'videoCount', 'postCount', 'likeCount', 'verseCount'];
    }

    public function getPictureCount()
    {
        return Resource::find()->where(["author_id" => Yii::$app->user->id, 'type' => 'picture'])->count();

    }
    public function getPolygenCount()
    {
        return Resource::find()->where(["author_id" => Yii::$app->user->id, 'type' => 'polygen'])->count();

    }
    public function getVideoCount()
    {
        return Resource::find()->where(["author_id" => Yii::$app->user->id, 'type' => 'video'])->count();

    }
    public function getPostCount()
    {
        return Message::find()->where(["author_id" => Yii::$app->user->id])->count();

    }
    public function getVerseCount()
    {

        return Verse::find()->where(["author_id" => Yii::$app->user->id])->count();

    }
    public function getLikeCount()
    {
        return Like::find()->where(['user_id' => Yii::$app->user->id])->count();
        //  return Message::find()->where(["author_id" => Yii::$app->user->id])->count();

        //return 1;
    }

}
