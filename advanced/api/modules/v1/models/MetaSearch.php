<?php

namespace api\modules\v1\models;

use api\modules\v1\models\Meta;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * MetaSearch represents the model behind the search form of `api\modules\v1\models\Meta`.
 */
class MetaSearch extends Meta
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'author_id', 'prefab', 'updater_id', 'image_id'], 'integer'],
            [['created_at', 'updated_at', 'title', 'info', 'data'], 'safe'],
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
        $query = Meta::find();

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
            'author_id' => $this->author_id,
            'updater_id' => $this->updater_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'prefab' => $this->prefab,
            'image_id' => $this->image_id,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'info', $this->info])
            ->andFilterWhere(['like', 'data', $this->data]);

        return $dataProvider;
    }
}
