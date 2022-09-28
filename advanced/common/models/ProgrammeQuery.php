<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[Programme]].
 *
 * @see Programme
 */
class ProgrammeQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Programme[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Programme|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
