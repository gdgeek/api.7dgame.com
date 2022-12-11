<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[MetaEvent]].
 *
 * @see MetaEvent
 */
class MetaEventQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return MetaEvent[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return MetaEvent|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
