<?php

namespace backend\modules\editor;

use yii\helpers\Url;

class Rotate
{
    public function setup($node, $inputs, $reader, $map)
    {
    }
}

class Property
{
    public function setup($node, $inputs, $reader, $map)
    {
        $this->key = $node->data->property->key;
        $this->value = $node->data->property->value;
    }
}

class Material
{
    public function setup($node, $inputs, $reader, $map)
    {
        //echo json_encode($node);
        $this->mode = $node->data->mode;
    }
}

class Toolbar
{
    public function setup($node, $inputs, $reader, $map)
    {
        $this->destroy = $node->data->destroy;
        $this->enabled = $node->data->enabled;
    }
}

class Native
{
    public function setup($node, $inputs, $reader, $map)
    {
        $this->type = $node->data->type;
        $this->name = $node->data->name;

        if (isset($inputs["transform"][0])) {
            $this->local = $inputs["transform"][0];
        }

        if (isset($inputs["propertis"])) {
            foreach ($inputs["propertis"] as $property) {
                $key = ($property->key);
                $this->$key = $property->value;
            }
        }
    }
}

class Local
{
    public function setup($node, $inputs, $reader, $map)
    {
        $multi = 1000;
        $this->position = $this->vector3($node->data->position, $multi);
        $this->scale = $this->vector3($node->data->scale, $multi);
        $this->angle = $this->vector3($node->data->angle, $multi);
        $this->multi = $multi;
        $this->effects = array();
    }

    public function vector3($v3, $multi)
    {
        $vector3 = new \stdClass();
        $vector3->x = round($v3[0] * $multi);
        $vector3->y = round($v3[1] * $multi);
        $vector3->z = round($v3[2] * $multi);
        return $vector3;
    }
}

class Output
{
    public function setup($node, $inputs, $reader, $map)
    {
        $this->list = $inputs['list'];
    }
}

class SampleEntity
{
    public function setup($node, $inputs, $reader, $map)
    {
        $this->data = null;
        if (isset($map[$node->id])) {
            $this->data = json_encode($reader->readData($map[$node->id], $map, 'sample'), JSON_UNESCAPED_SLASHES);
        }
    }
}

class SampleRoot
{
    public function setup($node, $inputs, $reader, $map)
    {
        $this->component = "MrPP.SampleLib.SampleStorable";
        $this->path = array("SampleRoot");

        $serialize = new \stdClass();
        $serialize->list = array();
        foreach ($inputs['samples'] as $key => $val) {
            array_push($serialize->list, $val->data);
        }
        $this->serialize = json_encode($serialize, JSON_UNESCAPED_SLASHES);
    }
}

class Polygen
{
    public function setup($node, $inputs, $reader, $map)
    {
        if (isset($node->data->id)) {
            $this->id = $node->data->id;
        } else {
            $this->id = "id";
        }

        $polygen = \api\modules\v1\models\Resource::findOne($node->data->mesh);
        if (!isset($polygen)) {
            return;
        }
        if (file_exists("uploads/" . $polygen->md5 . '.' . $polygen->type . ".gz")) {
            $compress = true;
        } else {
            $compress = false;
        }

        $file = new \stdClass();
        $file->name = '/uploads/' . $polygen->file_name . '.' . $polygen->type . ($compress ? '.gz' : '');
        $file->url = Url::home(true);
        $file->cache = true;
        $file->md5 = $polygen->md5;
        $file->compress = $compress;

        $this->file = $file;
        $this->type = "fbx";

        if (isset($inputs["transform"][0])) {
            $this->local = $inputs["transform"][0];
        }

        if (isset($inputs["material"][0])) {
            $this->material = $inputs["material"][0];
        }

        if (isset($inputs["propertis"])) {
            foreach ($inputs["propertis"] as $property) {
                $key = ($property->key);
                $this->$key = $property->value;
            }
        }
    }
}

class Sample
{
    public function setup($node, $inputs, $reader, $map)
    {
        $this->name = 'Title';
        if (isset($inputs["transform"][0])) {
            $this->local = $inputs["transform"][0];
        }

        if (isset($node->data->name)) {
            $this->name = $node->data->name;
        }

        $this->polygens = array();
        if (isset($inputs['polygen'])) {

            $this->polygens = $inputs['polygen'];
        }

        $this->natives = array();
        if (isset($inputs['native'])) {

            $this->natives = $inputs['native'];
        }

        if (isset($inputs['toolbar'][0])) {
            $this->toolbar = $inputs['toolbar'][0];
        }
        $this->hints = array();
        $this->boards = array();
    }
}

/**
 * reader module definition class
 */
class Reader extends \yii\base\Module

{

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'backend\modules\editor\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }

    private function readInputs($inputs, $nodes, $map)
    {
        $ret = array();
        foreach ($inputs as $name => $input) {
            $arr = array();
            foreach ($input->connections as $node) {
                $idx = $node->node;
                $r = $this->readNode($nodes->$idx, $nodes, $map);
                array_push($arr, $r);
            }
            $ret[$name] = $arr;
        }
        return $ret;
    }

    private function readNode($node, $nodes, $map)
    {
        $inputs = $this->readInputs($node->inputs, $nodes, $map);
        $class = 'backend\\modules\\editor\\' . $node->name;
        $data = new $class();
        $data->setup($node, $inputs, $this, $map);
        return $data;
    }

    public function configure($data, $map)
    {
        return $this->readData($data, $map, 'output');
    }

    public function readData($data, $map, $main)
    {
        foreach ($data->data->nodes as $node) {
            if (strtolower($node->name) == $main) {
                $ret = $this->readNode($node, $data->data->nodes, $map);
            }
        }
        return $ret;
    }

    public function sample($data)
    {
    }

    public function deconstruct($datas, $category)
    {
    }

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
        $ret = $this->configure($configure, $map);
        return $ret;
    }
}
