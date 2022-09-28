<?php

namespace backend\controllers;

use app\models\Editor;
use common\models\EditorData;
use common\models\Project;
use Yii;
use yii\web\Controller;

class EditorController extends \yii\web\Controller
{

    public function actionRemovedNode()
    {
        $project_id = Yii::$app->request->get('project_id');
        $post = Yii::$app->request->post();
        $editor_data = EditorData::find()->where(['node_id' => $post['node_id'], 'project_id' => $project_id])->one();
        if ($editor_data != null) {
            $editor_data->delete();
        }

        echo "cool"; //$project_id;
    }

    public function actionInternal()
    {

        $project_id = Yii::$app->request->get('project_id');
        $node_id = Yii::$app->request->get('node_id');
        $template = Yii::$app->request->get('template');
        $editor_data = EditorData::find()->where(['node_id' => $node_id, 'project_id' => $project_id])->one();
        $options = ['project_id' => $project_id, 'node_id' => $node_id, 'template' => $template];

        if ($editor_data) {
            $options['data'] = $editor_data->data;
        }

        return $this->render('internal', $options);
    }

    protected function clearProjectConfigure($project_id)
    {

        $project = $this->findProject($project_id);
        if ($project) {
            $project->configure = null;
            $project->save();
        }
    }
    protected function is_empty($json)
    {

        $obj = json_decode($json);

        if (isset($obj)) {
            foreach ($obj as $key => $value) {
                return false;
            }
        } else {
            return true;
        }

        return true;
    }

    public function actionDatabase()
    {
        $post = Yii::$app->request->post();
        $options = $post['options'];

        $editor = EditorData::find()->where(['node_id' => $options['node_id'], 'project_id' => $options['project_id']])->one();

        if ($editor) {
            $editor->user_id = $options['user_id'];
            $editor->type = $options['type'];
            $editor->data = $post['json'];
            $editor->serialization = $post['serialization'];
        } else {
            $editor = new EditorData();
            $editor->node_id = $options['node_id'];
            $editor->project_id = $options['project_id'];
            $editor->user_id = $options['user_id'];
            $editor->type = $options['type'];
            $editor->data = $post['json'];
            $editor->serialization = $post['serialization'];
        }

        $project = $this->findProject($options['project_id']);
        if ($project) {
            $project->configure = "{}";
            if (!$project->save()) {
                echo json_encode($project->getErrors()); //.json_encode($post['json']);
            }
        }
        if (!$editor->validate() || !$editor->save()) {

            echo json_encode($editor->getErrors()); //.json_encode($post['json']);
        }

    }

    public function actionIndex()
    {
        $project_id = Yii::$app->request->get('project');

        $template = Yii::$app->request->get('template');

        $editor = $this->findEditor($project_id, $template);
        if ($editor == null) {
            $editor = $this->createEditor($project_id, $template);
        }

        $post = Yii::$app->request->post();

        $datas = EditorData::find()->where(['project_id' => $project_id])->all();

        return $this->render('index', ['post' => $post, 'project_id' => $project_id, 'datas' => $datas]);

    }
    public function actionComponentJs()
    {
        return $this->renderPartial('component-js');
    }

    protected function createEditor($project, $template)
    {

        $editor = new Editor();
        $editor->project = $project;
        $editor->template = $template;
        if ($editor->validate()) {
            $editor->save();
        } else {
            echo json_encode($editor->getErrors());
        }
        return $editor;
    }

    protected function findEditor($project, $template)
    {
        if (($model = Editor::findOne(['project' => $project, 'template' => $template])) !== null) {
            return $model;
        }
        return null;
    }

    protected function findProject($id)
    {
        if (($model = Project::findOne($id)) !== null) {
            return $model;
        }
        return null;
    }

}
