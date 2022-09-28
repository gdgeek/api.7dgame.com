<?php

namespace app\components;

use yii\base\BaseObject;

use api\modules\v1\models\Resource;
use yii\helpers\Url;

/**
 * template module definition class
 */
class Template extends BaseObject
{
    public $template;
    public $project_id;
    public $node_id;

    public function __construct($template, $project_id, $node_id, $config = [])
    {
        $this->template = $template;
        $this->project_id = $project_id;
        $this->node_id = $node_id;
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        // custom initialization code goes here
    }

    public function setup()
    {
        $func = "_" . $this->template;
        return $this->$func($this->project_id, $this->node_id);
    }
    private function list($models){
		
		$list = array();
		foreach($models as $val){ 
			$data = new \stdClass();
			$data->id = $val->id;
			$data->name = $val->name;
			array_push($list, $data);
		} 
		return $list;
	}
    private function _sample($project_id, $node_id)
    {
        $data = new \stdClass();
        $data->title = '样品编辑';
        //注册插件
        $plugins = [];
        $plugins['DB'] = new \stdClass();
        $plugins['DB']->url = Url::toRoute('editor/database');
        $plugins['DB']->node_id = $node_id;
        $plugins['DB']->user_id = \Yii::$app->user->id;
        $plugins['DB']->project_id = $project_id;
        $plugins['DB']->type = 'Sample';



        $plugins['Export'] = new \stdClass();

        $plugins['Locked'] = new \stdClass();
        $plugins['Locked']->components = ['Sample' => [1, 1]];

        $plugins['Polygens'] = new \stdClass();
        $plugins['Polygens']->polygens = $this->list( Resource::find()->where(['author_id' => \Yii::$app->user->id, 'type' => 'polygen'])->all());
        $plugins['Polygens']->url = Url:: home(true);

        $plugins['Videos'] = new \stdClass();
        $plugins['Videos']->videos = $this->list( Resource::find()->where(['author_id' => \Yii::$app->user->id, 'type' => 'video'])->all());
        $plugins['Videos']->url = Url:: home(true);

        $plugins['Pictures'] = new \stdClass();
        $plugins['Pictures']->pictures = $this->list( Resource::find()->where(['author_id' => \Yii::$app->user->id, 'type' => 'picture'])->all());
        $plugins['Pictures']->url = Url:: home(true);
        /*
        $plugins['Sounds'] = new \stdClass();
        $plugins['Sounds']->sounds = $this->list( Sound::find()->where(['user_id' => \Yii::$app->user->id])->all());
        $plugins['Sounds']->url = Url:: home(true);
        */
        $data->parent = Url::toRoute(['editor/index', "project" => $project_id, 'template' => 'configure']);
        $data->plugins = $plugins;
        $data->data = '{"id":"MrPP@0.1.0","nodes":{"0":{"id":0,"data":{},"inputs":{"list":{"connections":[]}},"outputs":{},"position":[400,400],"name":"Sample"}}}';
      
        return $data;
    }

    private function _main($project_id, $node_id)
    {
        $data = new \stdClass();

        $data->title = '场景编辑';
        $plugins = [];

        $plugins['Internal'] = new \stdClass();
        $plugins['Internal']->url = Url::toRoute(['editor/internal', 'project_id' => $project_id]);
        $plugins['Internal']->removed_url = Url::toRoute(['editor/removed-node', 'project_id' => $project_id]);

        $plugins['Export'] = new \stdClass();


        $plugins['DB'] = new \stdClass();
        $plugins['DB']->url = Url::toRoute('editor/database');
        $plugins['DB']->node_id = $node_id;
        $plugins['DB']->user_id = \Yii::$app->user->id;
        $plugins['DB']->project_id = $project_id;
        $plugins['DB']->type = 'configure';
        $plugins['Node'] = new \stdClass();

        $plugins['Locked'] = new \stdClass();
        $plugins['Locked']->components = ['Output' => [1, 1], 'SampleRoot' => [0, 1]];

        $data->parent = Url::toRoute(['editor/index', "project" => $project_id, 'template' => 'configure']);
        $data->plugins = $plugins;
        $data->data = '{"id":"MrPP@0.1.0","nodes":{"1":{"id":1,"data":{},"inputs":{"list":{"connections":[]}},"outputs":{},"position":[400,400],"name":"Output"}}}';
        return $data;
    }
   

}
