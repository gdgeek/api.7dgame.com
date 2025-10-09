<?php

namespace api\modules\v1\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\modules\v1\models\Phototype;

/**
 * PhototypeSearch represents the model behind the search form of `api\modules\v1\models\Phototype`.
 */
class PhototypeSearch extends Phototype
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'author_id', 'image_id', 'updater_id'], 'integer'],
            [['title', 'data', 'style', 'created_at', 'updated_at', 'uuid'], 'safe'],
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
        $query = Phototype::find();

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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'image_id' => $this->image_id,
            'updater_id' => $this->updater_id,
        ]);
        
        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'data', $this->data])
            ->andFilterWhere(['like', 'schema', $this->schema])
            ->andFilterWhere(['like', 'uuid', $this->uuid]);

        return $dataProvider;
    }
}
