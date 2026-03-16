<?php

namespace common\models;

use yii\data\ActiveDataProvider;
use yii\base\Model;

/**
 * PluginPermissionConfigSearch represents the model behind the search form of `common\models\PluginPermissionConfig`.
 */
class PluginPermissionConfigSearch extends PluginPermissionConfig
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['role_or_permission', 'plugin_name', 'action'], 'safe'],
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
     * Creates data provider instance with search query applied.
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = PluginPermissionConfig::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'role_or_permission', $this->role_or_permission])
              ->andFilterWhere(['like', 'plugin_name', $this->plugin_name])
              ->andFilterWhere(['like', 'action', $this->action]);

        return $dataProvider;
    }
}
