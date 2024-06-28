<?php

namespace api\modules\v1\components;

use api\modules\v1\models\MultilanguageVerse;
use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

class MultilanguageVerseRule extends VerseDeriveRule
{
    public $name = 'multilanguage_verse_rule';
    public $modelType = MultilanguageVerse::class;
}
