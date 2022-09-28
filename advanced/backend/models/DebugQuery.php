<?php

namespace backend\models;

/**
 * This is the ActiveQuery class for [[Debug]].
 *
 * @see Debug
 */
class DebugQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Debug[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Debug|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
