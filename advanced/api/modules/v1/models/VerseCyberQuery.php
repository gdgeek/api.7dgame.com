<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[VerseCyber]].
 *
 * @see VerseCyber
 */
class VerseCyberQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return VerseCyber[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return VerseCyber|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
