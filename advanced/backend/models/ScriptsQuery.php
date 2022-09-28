<?php

namespace backend\models;

/**
 * This is the ActiveQuery class for [[Scripts]].
 *
 * @see Scripts
 */
class ScriptsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Scripts[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Scripts|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
