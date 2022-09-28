<?php

namespace api\modules\v1\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\modules\v1\models\Reply;

/**
 * ReplySearch represents the model behind the search form of `api\modules\v1\models\Reply`.
 */
class ReplySearch extends Reply
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'message_id', 'author_id', 'updater_id'], 'integer'],
            [['body', 'created_at', 'updated_at', 'info'], 'safe'],
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
        $query = Reply::find();

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
            'message_id' => $this->message_id,
            'author_id' => $this->author_id,
            'updater_id' => $this->updater_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'body', $this->body])
            ->andFilterWhere(['like', 'info', $this->info]);

        return $dataProvider;
    }
}
