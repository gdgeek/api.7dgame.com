<?php

namespace api\modules\vp\models;

/**
 * This is the ActiveQuery class for [[Guide]].
 *
 * @see VpGuide
 */
class GuideQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Guide[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Guide|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
