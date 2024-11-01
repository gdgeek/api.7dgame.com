<?php

namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;

class DocumentController extends \yii\web\Controller

{
    public $layout = '@backend/views/layouts/main.php';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['privacy'],
                'rules' => [
                    [
                        'actions' => ['privacy'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['privacy'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],

        ];
    }
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionPrivacy()
    {
        $this->layout = '@backend/views/layouts/black.php';
        return $this->render('privacy');
    }
    public function actionSchool()
    {
        return $this->render('school');
    }
    public function actionInfo()
    {

        $info = new \stdClass();
        $info->title = Yii::$app->params['information']['title'];
        $info->description = Yii::$app->params['information']['sub-title'];
        //$info->api = Yii::$app->params['information']['api'];
        //$info->pub = Yii::$app->params['information']['pub'];

        return json_encode($info);
    }
}
