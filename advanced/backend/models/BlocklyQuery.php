<?php

namespace backend\models;

/**
 * This is the ActiveQuery class for [[Blockly]].
 *
 * @see Blockly
 */
class BlocklyQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Blockly[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Blockly|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
