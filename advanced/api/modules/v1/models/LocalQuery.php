<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[Local]].
 *
 * @see Local
 */
class LocalQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Local[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Local|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
