<?php

namespace app\components;

use common\components\editor\NodeOutput;
use common\components\editor\NodeSampleEntity;
use yii\base\BaseObject;

/**
 * template module definition class
 */
class EditorComponent extends BaseObject
{
    protected $template;

    public function __construct($template, $config = [])
    {
        $this->template = $template;
        // ... 在应用配置之前初始化
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        // custom initialization code goes here
    }

    private function controlData($data)
    {
        $ret = new \stdClass();
        $ret->text = "";

        switch ($data['type']) {
            case 'key-string':
                $ret->text = "KeyStringControl(this.editor, '" . $data['name'] . "', '" . $data['title'] . "', " . json_encode($data["default"]) . ")";
                break;
            case 'key-bool':
                $ret->text = "KeyBoolControl(this.editor, '" . $data['name'] . "', '" . $data['title'] . "', " . json_encode($data["default"]) . ")";
                break;
            case 'string':
                $ret->text = "StringControl(this.editor, '" . $data['name'] . "', '" . $data['title'] . "', '" . $data['default'] . "')";
                break;
            case 'vector3':
                $ret->text = "Vector3Control(this.editor, '" . $data['name'] . "', '" . $data['title'] . "', " . json_encode($data["default"]) . ")";
                break;
            case 'vector2':
                $ret->text = "Vector2Control(this.editor, '" . $data['name'] . "', '" . $data['title'] . "', " . json_encode($data["default"]) . ")";
                break;
            case 'select':
                $ret->text = "SelectControl(this.editor, '" . $data['name'] . "','" . $data['title'] . "', " . json_encode($data["options"]) . ",'" . $data['default'] . "')";
                break;
            case 'bool':
                $ret->text = "BoolControl(this.editor, '" . $data['name'] . "', '" . $data['title'] . "', " . $data['default'] . ")";
                break;
            case 'number':
                $ret->text = "NumberControl(this.editor, '" . $data['name'] . "', '" . $data['title'] . "', " . $data['default'] . ")";
                break;
            case 'color-picker':
                $ret->text = "ColorPickerControl(this.editor, '" . $data['name'] . "', '" . $data['title'] . "', " . $data['default'] . ")";
                break;
            case 'resource':
                $ret->constructor = "let self = this;
                                    this." . $data['func'] . " = function (resources) {
                                                                    self.resources = resources;
                                                                 };";

                $ret->builder = "var options = Array();
                                options.push({text:'None', value:'" . \Yii::t('app/editor', 'None') . "'});
                                for (let i = 0; i < this.resources.length; ++i) {
                                    let op = {};
                                    op.value = this.resources[i].id;
                                    op.text = this.resources[i].id + ':' + this.resources[i].name;
                                    options.push(op);
                                }";

                $ret->text = "SelectControl(this.editor, '" . $data['name'] . "', '" . $data['title'] . "', options, '" . $data['default'] . "')";
                break;
            case 'link':
                $ret->text = "LinkControl(this.editor, '" . $data['name'] . "', node.id, '" . $data['title'] . "', '" . $data['default'] . "')";
                break;
            case 'edit':
                $ret->constructor = "this.push = null;
                                    this.removed = null;
                                    let self = this;
                                    this.setNodes = function (nodes) {
                                        self.nodes = nodes;
                                    };";

                $ret->builder = "var pack = {
                                push: this.push,
                                data: {template:'" . $data['template'] . "', node_id: node.id}
                                };";
                $ret->text = "EditorControl(this.editor, '" . $data['name'] . "', this.nodes[node.id], ' " . \Yii::t('app/editor', 'Edit') . " (id:'+node.id+')', function () {
                                this.push(this.data);
                                }.bind(pack))";
                break;
        }
        return $ret;
    }


    private function _SampleEntity()
    {
        return NodeSampleEntity::Data();
    }

    private function _Output()
    {
        return NodeOutput::Data();
    }

    public function components()
    {
        switch (strtolower($this->template)) {
            case "main":
                return ["Output", "SampleEntity"/*, "VideoEntity", "PictureEntity"*/];
            case "sample":
                return [
                    "Local", "Sample", "Point", "Transparent", "Web", "Polygen", "Video", "Picture", "Text", "Native",
                    "Tip", "Board", "Rotate", "Button", "Toolbar",  "Action", "BoolProperty", "FloatProperty",/* "Property",*/ "Mark", "ChartModule", /* "Options","PictureText"*/
                ];
        }
        return [];
    }

    private function toJs($data)
    {
        return $this->makeComponent($data);
    }

    private function makeComponent($data)
    {


        $name = $data['name'];
        $title = $data['title'];
        $controls = $data['controls'];
        $inputs = $data['inputs'];
        $output = $data['output'];

        $control_constructor = "";
        $control_builder = "";
        $control_text = "";
        foreach ($controls as $control) {
            $ret = $this->controlData($control);
            if (isset($ret->constructor)) {
                $control_constructor .= $ret->constructor;
            }
            if (isset($ret->constructor)) {
                $control_builder .= $ret->builder;
            }
            if (isset($ret->text)) {
                $control_text .= ".addControl(new " . $ret->text . ")";
            }
        }
        ob_start();
?>
        class <?= $name ?> extends Rete.Component {constructor() {
        super('<?= $name ?>');
        this.title = '<?= $title ?>';
        this.type = [];
        <?php if (isset($data['type'])) { ?>
            this.type = <?= json_encode($data['type']) ?>;
        <?php } ?>

        <?= $control_constructor ?>
        }
        builder(node) {
        node.title = this.title;
        node.type = this.type;
        <?= $control_builder ?>
        return node
        <?= $control_text ?>
        <?php
        foreach ($inputs as $input) {
        ?>
            .addInput(new Rete.Input("<?= $input['name'] ?>", "<?= $input['title'] . ($input['multiple'] ? "+" : "") ?>", <?= $input['socket'] ?>, <?= $input['multiple'] ? "true" : "false" ?>))
        <?php
        }
        ?>
        <?php
        if (isset($output)) {
        ?>
            .addOutput(new Rete.Output("<?= $output['name'] ?>", "<?= $output['title'] ?>", <?= $output['socket'] ?>))
        <?php
        }
        ?>
        ;
        }
        }
<?php
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }

    public function js()
    {
        $data = $this->components();
        $js = '';
        foreach ($data as $val) {
            $class = "common\\components\\editor\\Node" . $val;
            $js .= $this->toJs($class::Data());
        }
        return $js;
    }
}
