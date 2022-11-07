<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[Space]].
 *
 * @see Space
 */
class SpaceQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Space[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Space|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
