<?php

namespace backend\models;

/**
 * This is the ActiveQuery class for [[Logic]].
 *
 * @see Logic
 */
class LogicQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Logic[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Logic|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
