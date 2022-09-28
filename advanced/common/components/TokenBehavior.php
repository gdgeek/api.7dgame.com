<?php
namespace common\components;

use Yii;
use yii\base\ActionFilter;

class TokenBehavior extends ActionFilter
{
    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {

        $access = Yii::$app->user->identity->generateAccessToken();
        
        $headers->add('Mrpp-Token-Next', $access);
        return true;
    }
}