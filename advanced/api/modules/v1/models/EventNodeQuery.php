<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[EventNode]].
 *
 * @see EventNode
 */
class EventNodeQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return EventNode[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return EventNode|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
