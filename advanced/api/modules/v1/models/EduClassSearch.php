<?php

namespace api\modules\v1\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * EduClassSearch represents the model behind the search form of `api\modules\v1\models\EduClass`.
 */
class EduClassSearch extends EduClass
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'school_id', 'image_id'], 'integer'],
            [['name', 'info', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = EduClass::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            return $dataProvider;
        }

        // 按 school_id 过滤
        $query->andFilterWhere([
            'id' => $this->id,
            'school_id' => $this->school_id,
            'image_id' => $this->image_id,
        ]);

        // 按名称模糊搜索
        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['like', 'info', $this->info]);

        return $dataProvider;
    }
}
