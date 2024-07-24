<?php

namespace api\modules\vp\models;

/**
 * This is the ActiveQuery class for [[VpToken]].
 *
 * @see VpToken
 */
class VpTokenQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return VpToken[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return VpToken|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
