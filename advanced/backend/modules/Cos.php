<?php
/**
 * Created by PhpStorm.
 * User: cngua
 * Date: 2019/10/30
 * Time: 19:10
 */
namespace backend\modules;
use api\modules\v1\models\File;
use api\modules\v1\models\Resource;
use yii\db\Query;


class Cos extends \yii\base\Module
{
    public $controllerNamespace = 'backend\modules\controllers';

    public function init()
    {
        parent::init();
    }

    protected function getVideos()
    {
        $videos = Resource::find()->where(['author_id' => \Yii::$app->user->id, 'type'=>'video'])->all();
        return $videos;
    }

    public function videos()
    {
        $videos = $this->getVideos();
        $list = array();

        foreach ($videos as $video) {
            $url = (new Query())
                ->select('url')
                ->from('file')
                ->where(['author_id' => \Yii::$app->user->id, 'type'=>'video', 'id'=>$video->file_id])
                ->one();

            $file = new \stdClass();
            $file->user_id = $video->user_id;
            $file->file_name = $video->name;
            $file->file_id = $video->file_id;
            $file->url = $url;
            array_push($list, $file);
        }
        return $list;
    }
}