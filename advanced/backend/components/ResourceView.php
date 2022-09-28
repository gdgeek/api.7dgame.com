<?php

namespace backend\components;

use yii\base\Widget;

class ResourceView  extends Widget
{
    public $render = 'video_view';
    private $container;
    public $model;
    public function init()
    {
        $this->container = $this->render($this->render, ['model' => $this->model]);
        parent::init();
     
    }

    public function run()
    {     

        return $this->render('resource_view', ['model' => $this->model, 'container' => $this->container]);
    }
}
