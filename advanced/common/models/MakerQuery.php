<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[Maker]].
 *
 * @see Maker
 */
class MakerQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Maker[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Maker|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
