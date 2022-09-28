<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[Invitation]].
 *
 * @see Invitation
 */
class InvitationQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Invitation[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Invitation|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
