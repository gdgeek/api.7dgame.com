<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[VerseSpace]].
 *
 * @see VerseSpace
 */
class VerseSpaceQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return VerseSpace[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return VerseSpace|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
