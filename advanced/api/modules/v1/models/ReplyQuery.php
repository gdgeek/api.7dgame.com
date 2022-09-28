<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[Reply]].
 *
 * @see Reply
 */
class ReplyQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Reply[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Reply|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
