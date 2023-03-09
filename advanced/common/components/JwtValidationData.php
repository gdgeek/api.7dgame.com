<?php

namespace common\components;

class JwtValidationData extends \sizeg\jwt\JwtValidationData

{

    /**
     * @inheritdoc
     */
    public function init()
    {

        $this->validationData->setIssuer(\Yii::$app->jwt_parameter->issuer);
        $this->validationData->setAudience(\Yii::$app->jwt_parameter->audience);
        $this->validationData->setId(\Yii::$app->jwt_parameter->id);

        parent::init();
    }
}
