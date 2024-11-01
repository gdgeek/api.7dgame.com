<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[VpScore]].
 *
 * @see VpScore
 */
class VpScoreQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return VpScore[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return VpScore|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
