<?php


namespace api\modules\private\models;

/**
 * This is the ActiveQuery class for [[MultilanguageVerse]].
 *
 * @see MultilanguageVerse
 */
class MultilanguageVerseQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return MultilanguageVerse[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return MultilanguageVerse|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
