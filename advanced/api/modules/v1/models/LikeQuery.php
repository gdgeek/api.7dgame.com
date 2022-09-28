<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[Like]].
 *
 * @see Like
 */
class LikeQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Like[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Like|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
