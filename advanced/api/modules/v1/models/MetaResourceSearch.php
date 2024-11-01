<?php

namespace api\modules\v1\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\modules\v1\models\MetaResource;

/**
 * MetaResourceSearch represents the model behind the search form of `api\modules\v1\models\MetaResource`.
 */
class MetaResourceSearch extends MetaResource
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'meta_id', 'resource_id'], 'integer'],
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
        $query = MetaResource::find();

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
            'meta_id' => $this->meta_id,
            'resource_id' => $this->resource_id,
        ]);

        return $dataProvider;
    }
}
