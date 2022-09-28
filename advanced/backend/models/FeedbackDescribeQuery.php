<?php

namespace backend\models;

/**
 * This is the ActiveQuery class for [[FeedbackDescribe]].
 *
 * @see FeedbackDescribe
 */
class FeedbackDescribeQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return FeedbackDescribe[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return FeedbackDescribe|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
