<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[Version]].
 *
 * @see Version
 */
class VersionQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Version[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Version|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
