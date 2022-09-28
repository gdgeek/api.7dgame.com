<?php

namespace common\components;

class JwtValidationData extends \sizeg\jwt\JwtValidationData
{
 
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->validationData->setIssuer('https://api.mrpp.com');
        $this->validationData->setAudience('https://mrpp.com');
        $this->validationData->setId('4f1g23a12aa');

      		
        parent::init();
    }
}    