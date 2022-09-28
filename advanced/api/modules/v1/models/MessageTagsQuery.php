<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[MessageTags]].
 *
 * @see MessageTags
 */
class MessageTagsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return MessageTags[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return MessageTags|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
