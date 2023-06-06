<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[EventLink]].
 *
 * @see EventLink
 */
class EventLinkQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return EventLink[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return EventLink|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
