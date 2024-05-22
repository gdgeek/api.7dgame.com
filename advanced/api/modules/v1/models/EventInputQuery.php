<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[EventInput]].
 *
 * @see EventInput
 */
class EventInputQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return EventInput[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return EventInput|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
