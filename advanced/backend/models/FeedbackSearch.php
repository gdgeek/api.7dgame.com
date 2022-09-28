<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Feedback;

/**
 * FeedbackSearch represents the model behind the search form of `backend\models\Feedback`.
 */
class FeedbackSearch extends Feedback
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'reporter', 'repairer', 'describe_id'], 'integer'],
            [['bug', 'debug', 'infomation'], 'safe'],
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
        $query = Feedback::find();

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
            'reporter' => $this->reporter,
            'repairer' => $this->repairer,
            'describe_id' => $this->describe_id,
        ]);

        $query->andFilterWhere(['like', 'bug', $this->bug])
            ->andFilterWhere(['like', 'debug', $this->debug])
            ->andFilterWhere(['like', 'infomation', $this->infomation]);

        return $dataProvider;
    }
}
