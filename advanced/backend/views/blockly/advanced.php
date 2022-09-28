<?php

use yii\helpers\Url;
use backend\assets\BlocklyAsset;

$this->title = '脚本编辑';
$this->params['breadcrumbs'][] = $this->title;
BlocklyAsset::register($this);
?>

<?php $this->beginBlock('content-header'); ?>
    <h4><?= $this->title ?></h4>

    <div class="btn-group" role="group">
      <?php
     // echo  Yii::$app->params['information']['api'];
      ?>
        <a class="btn btn-info  btn-xs" id="save"> 逻辑保存 </a>
       <!-- <a class="btn btn-info  btn-xs" href="<?= Url::to('@web/blockly/demos/blockfactory/index.html') ?>"
           target="_blank"> 脚本导出 </a> --!>
        <a class="btn btn-success btn-xs"
           href="<?= Url::toRoute(['editor/index', 'project' => $project_id, 'template' => 'configure']); ?>"> 场景编辑 </a>
        <!--<a class="btn btn-success btn-xs" href="<?= Url::toRoute('method/create') ?>" > 脚本导入	</a> -->
    </div>

<?php $this->endBlock(); ?>
    <style>
        .content {
            min-height: 70vh;
        }
        .tab-pane {
        }
        #block_content {
            height: 70%;
            border-style: solid;
            border-width: 1px;
        }
    </style>

    <ul id="content" class="nav nav-tabs">
        <li class="active"><a href="#tab-content-blocks" data-toggle="tab" id="tab_blocks">编辑(Blockly)</a></li>
        <li><a href="#tab-content-lua" data-toggle="tab" id="tab_lua">脚本(Lua)</a></li>
    </ul>

    <div id="block_content" class="tab-content">
        <div class="tab-pane active" id="tab-content-blocks">
            <div id="content_blocks" class="content"></div>
        </div>

        <div class="tab-pane" id="tab-content-lua">
            <pre id="content_lua" class="content prettyprint lang-lua"></pre>
        </div>
    </div>
    <xml xmlns="https://developers.google.com/blockly/xml" id="toolbox" style="display: none">


    <category name="%{BKY_CATLOGIC}" colour="%{BKY_LOGIC_HUE}">
      <block type="controls_if"></block>
      <block type="logic_compare"></block>
      <block type="logic_operation"></block>
      <block type="logic_negate"></block>
      <block type="logic_boolean"></block>
      <block type="logic_null"></block>
      <block type="logic_ternary"></block>
    </category>

       <category name="%{BKY_CATLOOPS}" colour="%{BKY_LOOPS_HUE}">
      <block type="controls_repeat_ext">
        <value name="TIMES">
          <shadow type="math_number">
            <field name="NUM">10</field>
          </shadow>
        </value>
      </block>
      <block type="controls_whileUntil"></block>
      <block type="controls_for">
        <value name="FROM">
          <shadow type="math_number">
            <field name="NUM">1</field>
          </shadow>
        </value>
        <value name="TO">
          <shadow type="math_number">
            <field name="NUM">10</field>
          </shadow>
        </value>
        <value name="BY">
          <shadow type="math_number">
            <field name="NUM">1</field>
          </shadow>
        </value>
      </block>
      <block type="controls_forEach"></block>
      <block type="controls_flow_statements"></block>
    </category>
    <category name="%{BKY_CATMATH}" colour="%{BKY_MATH_HUE}">
      <block type="math_number">
        <field name="NUM">123</field>
      </block>
      <block type="math_arithmetic">
        <value name="A">
          <shadow type="math_number">
            <field name="NUM">1</field>
          </shadow>
        </value>
        <value name="B">
          <shadow type="math_number">
            <field name="NUM">1</field>
          </shadow>
        </value>
      </block>
      <block type="math_single">
        <value name="NUM">
          <shadow type="math_number">
            <field name="NUM">9</field>
          </shadow>
        </value>
      </block>
      <block type="math_trig">
        <value name="NUM">
          <shadow type="math_number">
            <field name="NUM">45</field>
          </shadow>
        </value>
      </block>
      <block type="math_constant"></block>
      <block type="math_number_property">
        <value name="NUMBER_TO_CHECK">
          <shadow type="math_number">
            <field name="NUM">0</field>
          </shadow>
        </value>
      </block>
      <block type="math_round">
        <value name="NUM">
          <shadow type="math_number">
            <field name="NUM">3.1</field>
          </shadow>
        </value>
      </block>
      <block type="math_on_list"></block>
      <block type="math_modulo">
        <value name="DIVIDEND">
          <shadow type="math_number">
            <field name="NUM">64</field>
          </shadow>
        </value>
        <value name="DIVISOR">
          <shadow type="math_number">
            <field name="NUM">10</field>
          </shadow>
        </value>
      </block>
      <block type="math_constrain">
        <value name="VALUE">
          <shadow type="math_number">
            <field name="NUM">50</field>
          </shadow>
        </value>
        <value name="LOW">
          <shadow type="math_number">
            <field name="NUM">1</field>
          </shadow>
        </value>
        <value name="HIGH">
          <shadow type="math_number">
            <field name="NUM">100</field>
          </shadow>
        </value>
      </block>
      <block type="math_random_int">
        <value name="FROM">
          <shadow type="math_number">
            <field name="NUM">1</field>
          </shadow>
        </value>
        <value name="TO">
          <shadow type="math_number">
            <field name="NUM">100</field>
          </shadow>
        </value>
      </block>
      <block type="math_random_float"></block>
      <block type="math_atan2">
        <value name="X">
          <shadow type="math_number">
            <field name="NUM">1</field>
          </shadow>
        </value>
        <value name="Y">
          <shadow type="math_number">
            <field name="NUM">1</field>
          </shadow>
        </value>
      </block>
    </category>
    <category name="%{BKY_CATTEXT}" colour="%{BKY_TEXTS_HUE}">
      <block type="text"></block>
      <block type="text_join"></block>
      <block type="text_append">
        <value name="TEXT">
          <shadow type="text"></shadow>
        </value>
      </block>
      <block type="text_length">
        <value name="VALUE">
          <shadow type="text">
            <field name="TEXT">abc</field>
          </shadow>
        </value>
      </block>
      <block type="text_isEmpty">
        <value name="VALUE">
          <shadow type="text">
            <field name="TEXT"></field>
          </shadow>
        </value>
      </block>
      <block type="text_indexOf">
        <value name="VALUE">
          <block type="variables_get">
            <field name="VAR">{textVariable}</field>
          </block>
        </value>
        <value name="FIND">
          <shadow type="text">
            <field name="TEXT">abc</field>
          </shadow>
        </value>
      </block>
      <block type="text_charAt">
        <value name="VALUE">
          <block type="variables_get">
            <field name="VAR">{textVariable}</field>
          </block>
        </value>
      </block>
      <block type="text_getSubstring">
        <value name="STRING">
          <block type="variables_get">
            <field name="VAR">{textVariable}</field>
          </block>
        </value>
      </block>
      <block type="text_changeCase">
        <value name="TEXT">
          <shadow type="text">
            <field name="TEXT">abc</field>
          </shadow>
        </value>
      </block>
      <block type="text_trim">
        <value name="TEXT">
          <shadow type="text">
            <field name="TEXT">abc</field>
          </shadow>
        </value>
      </block>
      <block type="text_print">
        <value name="TEXT">
          <shadow type="text">
            <field name="TEXT">abc</field>
          </shadow>
        </value>
      </block>
      <block type="text_prompt_ext">
        <value name="TEXT">
          <shadow type="text">
            <field name="TEXT">abc</field>
          </shadow>
        </value>
      </block>
    </category>
    <category name="%{BKY_CATLISTS}" colour="%{BKY_LISTS_HUE}">
      <block type="lists_create_with">
        <mutation items="0"></mutation>
      </block>
      <block type="lists_create_with"></block>
      <block type="lists_repeat">
        <value name="NUM">
          <shadow type="math_number">
            <field name="NUM">5</field>
          </shadow>
        </value>
      </block>
      <block type="lists_length"></block>
      <block type="lists_isEmpty"></block>
      <block type="lists_indexOf">
        <value name="VALUE">
          <block type="variables_get">
            <field name="VAR">{listVariable}</field>
          </block>
        </value>
      </block>
      <block type="lists_getIndex">
        <value name="VALUE">
          <block type="variables_get">
            <field name="VAR">{listVariable}</field>
          </block>
        </value>
      </block>
      <block type="lists_setIndex">
        <value name="LIST">
          <block type="variables_get">
            <field name="VAR">{listVariable}</field>
          </block>
        </value>
      </block>
      <block type="lists_getSublist">
        <value name="LIST">
          <block type="variables_get">
            <field name="VAR">{listVariable}</field>
          </block>
        </value>
      </block>
      <block type="lists_split">
        <value name="DELIM">
          <shadow type="text">
            <field name="TEXT">,</field>
          </shadow>
        </value>
      </block>
      <block type="lists_sort"></block>
    </category>
  


    
    <sep></sep>

        <?php
       
        $color = array();
        
        $color['Trigger'] = 0;
        $color['Entity'] = 50;
        $color['Data'] = 200;
        $color['Execute'] = 100;
        $color['Method'] = 300;
        $types = array();
        foreach ($blocklies as $blockly) {
            if(!isset($color[$blockly->type])){
               continue;
            }
            $data = json_decode($blockly->block);
            if(is_null($data)){
                $this->registerJs(
<<<JS
	Blockly.Blocks['$blockly->title'] = $blockly->block;
	Blockly.Lua['$blockly->title'] = $blockly->lua;
JS
                , $this::POS_READY
                );

             
            }else{
                $data->colour = $color[$blockly->type];
                $block = json_encode($data);
                $this->registerJs(
<<<JS
	Blockly.Blocks['$blockly->title'] = {
      init: function() {
        this.jsonInit(
           $block 
        );
      }
    }
	Blockly.Lua['$blockly->title'] = $blockly->lua;
JS
                , $this::POS_READY
                );

              
            }
            if (!isset($types[$blockly->type])) {
                $types[$blockly->type] = array();
            }
            $item = new \stdClass();
            $item->title = $blockly->title;
            $item->value = $blockly->value;
            array_push($types[$blockly->type], $item);

          
        }
        foreach($color as $key => $value){
        
           echo "<category name='" . \Yii::t('app', $key) . "' colour='" . $value . "'>";
           $list = $types[$key];
           if(isset($list)){
                foreach($list as $v){
                     echo "<block type='".$v->title."'>".$v->value."</block>";
                }
           
           }
          
            echo "</category>";
        }
      
        ?>
       
    <sep></sep>
    <category name="%{BKY_CATVARIABLES}" colour="%{BKY_VARIABLES_HUE}" custom="VARIABLE"></category>
    <category name="%{BKY_CATFUNCTIONS}" colour="%{BKY_PROCEDURES_HUE}" custom="PROCEDURE"></category>
    </xml>
<?php
$postUrl = Url::toRoute(['blockly/advanced-save', 'project_id' => $project_id]);

$this->registerJs(
<<<JS

BlocklyStorage.dom = '$dom';
$("#save").click(function(){
//alert('$postUrl');
	var workspace =  Blockly.getMainWorkspace();
	var xml = Blockly.Xml.workspaceToDom(workspace, true);
	if (workspace.getTopBlocks(false).length == 1 && xml.querySelector) {
		var block = xml.querySelector('block');
		if (block) {
			block.removeAttribute('x');
			block.removeAttribute('y');
		}
	}
	var dom = Blockly.Xml.domToText(xml);
	var code = Blockly.Lua.workspaceToCode(workspace);
	$.ajax({
		  type: 'POST',
		  url: '$postUrl',
		  data: {'dom':dom, 'code':code, 'project_id':$project_id},
		  success: function(data){
		      console.log( data);
		  },
		  error:function(data){
		      console.log( data);
		  },
		  dataType: 'text',
	});
});
Blockly.BlockSvg.START_HAT = true;
JS
    , $this::POS_READY
);
?>