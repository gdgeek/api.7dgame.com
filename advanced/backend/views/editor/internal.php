<?php

use app\components\Template;
use app\components\EditorComponent;
use rmrevin\yii\fontawesome\FA;
use backend\assets\editor\ReteAsset;
use backend\assets\editor\BaseAsset;

ReteAsset::register($this);
BaseAsset::register($this);

$module = new Template($template, $project_id, $node_id);
$setup = $module->setup();

$ec = new EditorComponent($template);
$components = [];

$components = $ec->components();
$this->registerJs($ec->js(), \yii\web\View::POS_BEGIN);
$plugins = $setup->plugins;
$this->title = $setup->title;
?>

<?php $this->beginBlock('content-header'); ?>
<script>
    function parent() {
        $(location).attr("href", "<?= $setup->parent ?>");
    }

    function back() {
        MrPP.DB.save();
        setTimeout("parent()", 150);
    }

    function arrange() {

        console.log('Arranging...')
        // MrPP.Arrange();
        MrPP.editor.trigger('arrange', MrPP.editor.nodes)

    }
</script>

<?= $setup->title ?><br>
<div class="btn-group" role="group" aria-label="...">
    <a class="btn btn-primary btn-xs" onclick="back()"><?= FA::icon('arrow-circle-left') ?> 返回</a>

    <a id="arrange" class="btn btn-success btn-xs" onclick="arrange()"> 节点整理</a>
    <a id="preview" class="btn btn-success btn-xs" onclick="preview()"> 场景预览</a>
    <a id="editor" class="btn btn-success btn-xs disabled" onclick="editor()"> 编辑界面</a>
    <a id="save" onclick="MrPP.DB.save()" class="btn btn-success btn-xs disabled" type="button" href="#">
        <?= FA::icon('save') ?>
        <nobr id="save_text">保存</nobr>
    </a>

</div>
<?php $this->endBlock(); ?>


<script>
    AFRAME.registerComponent('log', {
        schema: {
            type: 'string'
        },

        init: function() {
            var stringToLog = this.data;
            console.log(stringToLog);
        }
    });

    AFRAME.registerComponent('reader', {
        schema: {
            type: 'string'
        },

        render: function(entity, node, root) {
            const local = node.inputs["transform"];
            const transform = root.nodes[local.connections[0].node];

            console.log(transform.data);

            entity.object3D.position.set(
                parseFloat(transform.data.position[0]),
                parseFloat(transform.data.position[1]),
                parseFloat(transform.data.position[2])
            );


            entity.object3D.scale.set(
                parseFloat(transform.data.scale[0]),
                parseFloat(transform.data.scale[1]),
                parseFloat(transform.data.scale[2])
            );

            entity.object3D.rotation.set(
                parseFloat(transform.data.angle[0])/57.29578,
                parseFloat(transform.data.angle[1])/57.29578,
                parseFloat(transform.data.angle[2])/57.29578);

            entity.setAttribute('id', node.data.title);
            if (node.name.toLowerCase() === 'polygen') {
                
                console.log(node);    
                entity.setAttribute('gltf-model', this.assets[node.data.mesh]);
            }


        },
        draw: function() {
            const root = MrPP.editor.toJSON();
            const stack = new Array();
            stack.push({
                node: root.nodes[0],
                el: this.el
            });

            while (stack.length != 0) {
                const item = stack.pop();
                const entity = document.createElement('a-entity');
                this.render(entity, item.node, root);
                item.el.appendChild(entity);


                for (let n in item.node.inputs) {
                    const input = item.node.inputs[n];
                    if (n === "point") {
                        for (let c in input.connections) {
                            const connection = input.connections[c];
                            stack.push({
                                node: root.nodes[connection.node],
                                el: entity
                            });
                        }
                    }

                }
                /**/

            }

        },
        init: function() {
            const self = this;
            this.load = function(data){
                self.assets = [];
                for(let i = 0; i<data.detail.length; ++i){
                    const item = data.detail[i];
                    console.log(data.detail);
                    self.assets[item.id] =  item.file.url;
                }

                self.draw();

            }
           // var stringToLog = this.data;
            var el = this.el; // Reference to the component's entity.

            el.addEventListener('load', this.load);
          //  this.assets = document.createElement('a-assets');
           // el.appendChild(this.assets);



        },
        /**
         * Handle component removal.
         */
        remove: function() {
            var data = this.data;
            var el = this.el;

            el.removeEventListener('load', this.load.bind(this));

        }


    });

    function new_loader() {

        return {
            load(data) {
                this.stack = new Array();
                this.stack.push(data.nodes[0]);

                while (this.stack.length != 0) {
                    const node = this.stack.pop();

                    for (let n in node.inputs) {
                        const input = node.inputs[n];
                        if (n === "point") {
                            console.log(input);
                            for (let c in input.connections) {
                                const connection = input.connections[c];
                                this.stack.push(data.nodes[connection.node]);
                            }
                        }

                    }

                }

            },
            loaded(func) {

                this._loaded = func;
            },

        };
    }
   


    function editor() {

        $('#rete').show();
        $('#aframe').hide();
        $('#editor').addClass("disabled");
        $('#arrange').removeClass("disabled");
        $('#preview').removeClass("disabled");

    }

    function preview() {

        $("#draw").html('<a-scene reader="my test !!!" background="color: #E0FFFF"  embedded="" style="height:100%;width:100%"></a-scene>');
        $('#rete').hide();
        $('#aframe').show();
        $('#editor').removeClass("disabled");
        $('#arrange').addClass("disabled");
        $('#preview').addClass("disabled");
        $.get("https://api.mrpp.com/polygens?expand=file", function(result) {
            var sceneEl = document.querySelector('a-scene');
            sceneEl.emit('load', result);
        });
    }

    $(document).ready(function() {
        $('#aframe').hide();
    });
</script>

<?php echo $this->render('_edit', ['project_id' => 1, 'components' => $components, 'data' => (isset($data) ? $data : $setup->data), 'plugins' => $plugins]); ?>


<!-- Main content -->
<section class="content " style="height:100%; width:100%" id="aframe">
    <div class="row" style=" width:100%">
        <div style="height:100%; width:100%">
            <div class="box" style="height:100%">
                <div id='draw' class="box-body" style="height:100%"></div>
                <!-- ./box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</section>