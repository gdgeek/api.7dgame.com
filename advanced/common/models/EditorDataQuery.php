<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[EditorData]].
 *
 * @see EditorData
 */
class EditorDataQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return EditorData[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return EditorData|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
