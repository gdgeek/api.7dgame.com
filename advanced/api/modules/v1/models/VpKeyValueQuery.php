<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[VpKeyValue]].
 *
 * @see VpKeyValue
 */
class VpKeyValueQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return VpKeyValue[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return VpKeyValue|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
