<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[EventOutput]].
 *
 * @see EventOutput
 */
class EventOutputQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return EventOutput[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return EventOutput|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
