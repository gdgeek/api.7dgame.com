<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Editor]].
 *
 * @see Editor
 */
class EditorQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Editor[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Editor|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
