<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[MetaResource]].
 *
 * @see MetaResource
 */
class MetaResourceQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return MetaResource[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return MetaResource|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
