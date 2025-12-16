<?php

namespace api\modules\v1\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\modules\v1\models\Group;

/**
 * GroupSearch represents the model behind the search form of `api\modules\v1\models\Group`.
 */
class GroupSearch extends Group
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'image_id'], 'integer'],
            [['name', 'description', 'info', 'created_at', 'updated_at'], 'safe'],
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
     * @param int|null $classId Optional class ID to filter groups by class
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Group::find();

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
            'group.id' => $this->id,
            'group.user_id' => $this->user_id,
            'group.image_id' => $this->image_id,
            'group.created_at' => $this->created_at,
            'group.updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'group.name', $this->name])
            ->andFilterWhere(['like', 'group.description', $this->description])
            ->andFilterWhere(['like', 'group.info', $this->info]);

        return $dataProvider;
    }
}
