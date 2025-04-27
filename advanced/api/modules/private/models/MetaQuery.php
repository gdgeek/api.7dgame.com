<?php

namespace api\modules\private\models;

/**
 * This is the ActiveQuery class for [[Meta]].
 *
 * @see Meta
 */
class MetaQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Meta[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Meta|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
