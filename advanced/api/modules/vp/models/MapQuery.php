<?php

namespace api\modules\vp\models;

/**
 * This is the ActiveQuery class for [[Map]].
 *
 * @see Map
 */
class MapQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Map[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Map|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
