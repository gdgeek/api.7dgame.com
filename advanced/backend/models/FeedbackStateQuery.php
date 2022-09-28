<?php

namespace backend\models;

/**
 * This is the ActiveQuery class for [[FeedbackState]].
 *
 * @see FeedbackState
 */
class FeedbackStateQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return FeedbackState[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return FeedbackState|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
