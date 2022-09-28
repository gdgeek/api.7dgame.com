<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[VerseOpen]].
 *
 * @see VerseOpen
 */
class VerseOpenQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return VerseOpen[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return VerseOpen|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
