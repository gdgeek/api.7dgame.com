<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[AuthAssignment]].
 *
 * @see AuthAssignment
 */
class AuthItemQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return AuthAssignment[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return AuthAssignment|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
