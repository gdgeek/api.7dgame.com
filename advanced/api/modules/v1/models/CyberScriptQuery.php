<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[CyberScript]].
 *
 * @see CyberScript
 */
class CyberScriptQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return CyberScript[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return CyberScript|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
