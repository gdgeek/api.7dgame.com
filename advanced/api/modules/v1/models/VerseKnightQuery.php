<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[VerseKnight]].
 *
 * @see VerseKnight
 */
class VerseKnightQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return VerseKnight[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return VerseKnight|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
