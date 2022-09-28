<?php

namespace backend\models;

/**
 * This is the ActiveQuery class for [[FileReference]].
 *
 * @see FileReference
 */
class FileReferenceQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return FileReference[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return FileReference|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
