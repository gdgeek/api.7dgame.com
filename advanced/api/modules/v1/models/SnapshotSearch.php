<?php

namespace api\modules\v1\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\modules\v1\models\Snapshot;

/**
 * SnapshotSearch represents the model behind the search form of `api\modules\v1\models\Snapshot`.
 */
class SnapshotSearch extends Snapshot
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'verse_id', 'author_id', 'created_by'], 'integer'],
            [['name', 'description', 'uuid', 'code', 'data', 'image', 'metas', 'resources', 'created_at', 'type'], 'safe'],
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
        $query = Snapshot::find();

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
            'created_at' => $this->created_at,
            'author_id' => $this->author_id,
            'created_by' => $this->created_by,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'uuid', $this->uuid])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'data', $this->data])
            ->andFilterWhere(['like', 'image', $this->image])
            ->andFilterWhere(['like', 'metas', $this->metas])
            ->andFilterWhere(['like', 'resources', $this->resources])
            ->andFilterWhere(['like', 'type', $this->type]);

        return $dataProvider;
    }
}
