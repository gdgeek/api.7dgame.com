<?php

namespace backend\models;

/**
 * This is the ActiveQuery class for [[ScriptData]].
 *
 * @see ScriptData
 */
class ScriptDataQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return ScriptData[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ScriptData|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
