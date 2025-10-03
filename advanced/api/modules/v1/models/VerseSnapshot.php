<?php

namespace api\modules\v1\models;

use api\modules\v1\models\File;
use api\modules\v1\models\User;
use api\modules\v1\models\Tags;
use api\modules\v1\models\VerseTags;
use api\modules\v1\models\VerseCode;
use yii\db\ActiveQuery;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
* This is the model class for table "verse".
*
* @property int $id
* @property int $author_id
* @property int|null $updater_id
* @property string $created_at
* @property string $updated_at
* @property string $name
* @property string|null $info
* @property int|null $image_id
* @property string|null $data
* @property int|null $version
* @property string|null $description
*
* @property Manager[] $managers
* @property Meta[] $metas
* @property User $author
* @property File $image_id0
* @property User $updater

*/
class VerseSnapshot extends Verse
{
    public function fields(): array
    {
        return [];
    }

   public function extraFields()
    {


        return [
            'id',
            'name',
            'uuid',
            'metas' => function (): array {
                return $this->getMetas()->all();
            },
            'data',
            'image',
            'resources',
            'description',
            'code',
            'managers'
        ];
    }

    public function getCode()
    {
        $code = $this->getVerseCode()->one();
        $cl = Yii::$app->request->get('cl');

        $substring = "";
        if (!$cl || $cl != 'js') {
            $cl = 'lua';
            $substring = "local verse = {}\n local is_playing = false\n";
        }
        if ($code && $code->code) {
            $script = $code->code->$cl;
        }

        if (isset($script)) {
            if (strpos($script, $substring) !== false) {
                return $script;
            } else {
                return $substring . $script;
            }
        } else {
            return $substring;
        }
    }
    public function getMetas(): ActiveQuery
    {
        
       // return $this->hasMany(MetaSnapshot::className(), ['id' => 'meta_id'])
        //    ->viaTable('verse_meta', ['verse_id' => 'id']);
        return MetaSnapshot::find()->where(['id' => $this->getMetaIds()]);

    }


       /** 
     * Gets query for [[Managers]]. 
     * 
     * @return \yii\db\ActiveQuery 
     */
    public function getManagers(): ActiveQuery
    {
        return $this->hasMany(ManagerSnapshot::className(), ['verse_id' => 'id']);
    }
}
