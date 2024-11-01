<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[VerseMeta]].
 *
 * @see VerseMeta
 */
class VerseMetaQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return VerseMeta[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return VerseMeta|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
