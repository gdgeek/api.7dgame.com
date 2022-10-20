<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[MetaKnight]].
 *
 * @see MetaKnight
 */
class MetaKnightQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return MetaKnight[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return MetaKnight|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
