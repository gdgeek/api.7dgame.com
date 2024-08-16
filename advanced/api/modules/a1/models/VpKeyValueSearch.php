<?php

namespace api\modules\a1\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\modules\a1\models\VpKeyValue;

/**
 * VpKeyValueSearch represents the model behind the search form of `api\modules\v1\models\VpKeyValue`.
 */
class VpKeyValueSearch extends VpKeyValue
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['key', 'value'], 'safe'],
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
        $query = VpKeyValue::find();

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
        //vision, minimum,note

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->orFilterWhere(['like', 'key', $this->key])
            ->orFilterWhere(['like', 'value', $this->value]);

        return $dataProvider;
    }
}
