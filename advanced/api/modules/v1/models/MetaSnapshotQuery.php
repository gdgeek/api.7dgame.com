<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[MetaSnapshot]].
 *
 * @see MetaSnapshot
 */
class MetaSnapshotQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return MetaSnapshot[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return MetaSnapshot|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
