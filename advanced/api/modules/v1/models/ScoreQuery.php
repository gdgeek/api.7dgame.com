<?php

namespace api\modules\v1\models;

/**
 * This is the ActiveQuery class for [[Score]].
 *
 * @see Score
 */
class ScoreQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Score[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Score|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
