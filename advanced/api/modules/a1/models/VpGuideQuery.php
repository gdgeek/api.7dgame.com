<?php

namespace api\modules\a1\models;

/**
 * This is the ActiveQuery class for [[VpGuide]].
 *
 * @see VpGuide
 */
class VpGuideQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return VpGuide[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return VpGuide|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
