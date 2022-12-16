<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[VerseEvent]].
 *
 * @see VerseEvent
 */
class VerseEventQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return VerseEvent[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return VerseEvent|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
