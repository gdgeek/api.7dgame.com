<?php

namespace backend\models;

/**
 * This is the ActiveQuery class for [[Method]].
 *
 * @see Method
 */
class MethodQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Method[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Method|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
