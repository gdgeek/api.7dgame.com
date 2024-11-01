<?php

namespace api\modules\a1\models;

/**
 * This is the ActiveQuery class for [[VerseScript]].
 *
 * @see VerseScript
 */
class VerseScriptQuery extends \yii\db\ActiveQuery

{
    /*public function active()
    {
    return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return VerseScript[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return VerseScript|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
