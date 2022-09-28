<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[ContentType]].
 *
 * @see ContentType
 */
class ContentTypeQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return ContentType[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ContentType|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
