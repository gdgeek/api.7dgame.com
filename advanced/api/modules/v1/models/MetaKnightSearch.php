<?php

namespace api\modules\v1\models;

use api\modules\v1\models\MetaKnight;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * MetaKnightSearch represents the model behind the search form of `api\modules\v1\models\MetaKnight`.
 */
class MetaKnightSearch extends MetaKnight
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'verse_id', 'knight_id', 'user_id'], 'integer'],
            [['info', 'create_at', 'uuid'], 'safe'],
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
        $query = MetaKnight::find();

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
            'verse_id' => $this->verse_id,
            'knight_id' => $this->knight_id,
            'user_id' => $this->user_id,
            'create_at' => $this->create_at,
        ]);

        $query->andFilterWhere(['like', 'info', $this->info])
            ->andFilterWhere(['like', 'uuid', $this->uuid]);

        return $dataProvider;
    }
}
