<?php

namespace api\modules\v1\components;

use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

use api\modules\v1\models\VerseScript;
use api\modules\v1\components\rule\VerseDeriveRule;

class ScriptVerseRule extends VerseDeriveRule
{
    public $name = 'script_verse_rule';
    public $modelType = VerseScript::class;
}
