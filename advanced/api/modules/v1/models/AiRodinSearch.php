<?php

namespace api\modules\v1\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\modules\v1\models\AiRodin;

/**
* AiRodinSearch represents the model behind the search form of `api\modules\v1\models\AiRodin`.
*/
class AiRodinSearch extends AiRodin
{
    /**
    * {@inheritdoc}
    */
    public function rules()
    {
        return [
            [['id', 'user_id', 'resource_id'], 'integer'],
            [['created_at', 'generation', 'check', 'download', 'query', 'name'], 'safe'],
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
        $query = AiRodin::find();
        
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
            'user_id' => $this->user_id,
            'resource_id' => $this->resource_id,
        ]);
        
        $query->andFilterWhere(['like', 'generation', $this->generation])
        ->andFilterWhere(['like', 'check', $this->check])
        ->andFilterWhere(['like', 'download', $this->download])
        ->andFilterWhere(['like', 'query', $this->query])
        ->andFilterWhere(['like', 'name', $this->name]);
        
        return $dataProvider;
    }
}
