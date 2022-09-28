<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[Verse]].
 *
 * @see Verse
 */
class VerseQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Verse[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Verse|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
