<?php

namespace api\modules\v1\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\modules\v1\models\VerseRelease;

/**
 * VerseReleaseSearch represents the model behind the search form of `api\modules\v1\models\VerseRelease`.
 */
class VerseReleaseSearch extends VerseRelease
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'lifetime', 'verse_id'], 'integer'],
            [['code', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = VerseRelease::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'lifetime' => $this->lifetime,
            'verse_id' => $this->verse_id,
        ]);

        $query->andFilterWhere(['like', 'code', $this->code]);

        return $dataProvider;
    }
}
