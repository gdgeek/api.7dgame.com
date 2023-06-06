<?php

namespace api\modules\v1\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\modules\v1\models\VerseScript;

/**
 * VerseScriptSearch represents the model behind the search form of `api\modules\v1\models\VerseScript`.
 */
class VerseScriptSearch extends VerseScript
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'verse_id'], 'integer'],
            [['created_at', 'script'], 'safe'],
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
        $query = VerseScript::find();

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
            'verse_id' => $this->verse_id,
        ]);

        $query->andFilterWhere(['like', 'script', $this->script]);

        return $dataProvider;
    }
}
