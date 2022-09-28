<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[ProjectData]].
 *
 * @see ProjectData
 */
class ProjectDataQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return ProjectData[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ProjectData|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
