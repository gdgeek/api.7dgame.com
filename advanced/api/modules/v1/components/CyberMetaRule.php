<?php

namespace api\modules\v1\components;

use api\modules\v1\models\Cyber;
use api\modules\v1\components\rule\MetaDeriveRule;
use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

class CyberMetaRule extends MetaDeriveRule
{
    public $name = 'cyber_meta_rule';
    public $modelType = Cyber::class;
}
