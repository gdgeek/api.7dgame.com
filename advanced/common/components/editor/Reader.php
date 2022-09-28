<?php

namespace common\components\editor;

use yii\base\BaseObject;
use \common\models\EditorData;
/**
 * reader module definition class
 */
class Reader extends BaseObject
{
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function init()
    {
        parent::init();
    }

    /**
     * @param $inputs
     * @param $nodes
     * @param $map
     * @param $node_id
     * @param $category
     * @return array
     */
    private function readInputs($inputs, $nodes, $map, $node_id, &$category)
    {
        $ret = array();
        foreach ($inputs as $name => $input) {
            $arr = array();
            foreach ($input->connections as $node) {
                $idx = $node->node;
                $r = $this->readNode($nodes->$idx, $nodes, $map, $node_id, $category);
                array_push($arr, $r);
            }
            $ret[$name] = $arr;
        }
        return $ret;
    }

    private function readNode($node, $nodes, $map, $node_id, &$category)
    {
        $inputs = $this->readInputs($node->inputs, $nodes, $map, $node_id, $category);
        $class = 'common\\components\\editor\\Node' . $node->name;

        $data = new $class();
        if (isset($category[$node->name])) {
            array_push($category[$node->name], $class::GetLabel($node, $node_id));
        }
        $data->setup($node, $inputs, $this, $map, $node_id, $category);
        return $data;
    }

    /**
     * @param $configure
     * @param $map
     * @param $category
     * @return mixed|void
     */
    public function configure($configure, $map, &$category)
    {
        return $this->readData($configure, $map, 'output', -1, $category);
    }

    /**
     * @param $data
     * @param $map
     * @param $main
     * @param $node_id
     * @param $category
     * @return mixed|void
     */
    public function readData($data, $map, $main, $node_id, &$category)
    {
        if (!isset($data) || !isset($data->data) || !isset($data->data->nodes)) {
            return;
        }
        foreach ($data->data->nodes as $node) {
            if (strtolower($node->name) == $main) {
                $ret = $this->readNode($node, $data->data->nodes, $map, $node_id, $category);
                return $ret;
            }
        }
    }

    /**
     * @param $project_id ����ID
     * @return array
     */
    public function getDatas($project_id)
    {
        $jsons = EditorData::findAll(['project_id' => $project_id]);
        $datas = array();
        foreach ($jsons as $key => $val) {
            $data = new \stdClass();
            $data->type = $val->type;
            $data->node_id = $val->node_id;
            $data->data = json_decode($val->data);
            array_push($datas, $data);
        }
        return $datas;
    }

    public function deconstruct($datas, $c)
        {
        $map = array();
        $configure = null;
        foreach ($datas as $key => $data) {
            if (strtolower($data->type) == 'configure') {
                $configure = $data;
            } else {
                $map[$data->node_id] = $data;
            }
        }
        $category = array();
        foreach ($c as $data) {
            $category[$data] = array();
        }
        $ret = $this->configure($configure, $map, $category);
        return $category;
    }

    /**
     * @param $datas
     * @return mixed|void
     */
    public function reader($datas)
    {
        $map = array();
        $configure = null;
        foreach ($datas as $key => $data) {
            if (strtolower($data->type) == 'configure') {
                $configure = $data;
            } else {
                $map[$data->node_id] = $data;
            }
        }
        $category = array();
        $ret = $this->configure($configure, $map, $category);
        return $ret;
    }
}
