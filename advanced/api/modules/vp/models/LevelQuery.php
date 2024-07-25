<?php

namespace api\modules\vp\models;

/**
 * This is the ActiveQuery class for [[Level]].
 *
 * @see Level
 */
class LevelQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Level[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Level|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
