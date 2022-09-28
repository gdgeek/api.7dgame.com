<?php

namespace backend\modules;

/**
 * Reporter module definition class
 */
class Reporter extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'backend\modules\controllers';
	
	public $feedback = null;
	//public $states = null;
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
		
	//	$this->feedback = 
		
	//	$this->states = \backend\models\FeedbackState::find()->all();
		

        // custom initialization code goes here
    }
//	public function doit(){
	//	echo 'fffff';
	//}

	public function getDescribe(){
		$list = [];
		$describe = \backend\models\FeedbackDescribe::find()->all();
		foreach($describe as $value){
			$list[$value->id] = $value->describe;
		}
		return $list;

	}
	public function getStates(){
		$list = [];
		$states = \backend\models\FeedbackState::find()->all();
		foreach($states as $value){
			$list[$value->id] = $value->state;
		}
		return $list;
	}
	public function getFeedback(){
		return new \backend\models\Feedback();
	}
}
