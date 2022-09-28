<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[Trade]].
 *
 * @see Trade
 */
class TradeQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Trade[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Trade|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
