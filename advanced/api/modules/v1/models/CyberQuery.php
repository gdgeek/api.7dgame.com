<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[Cyber]].
 *
 * @see Cyber
 */
class CyberQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Cyber[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Cyber|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
