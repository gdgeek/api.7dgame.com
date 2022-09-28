<?php
 use dosamigos\ckeditor\CKEditor;

 
use yii\helpers\Html;
use yii\widgets\ActiveForm;
// First we need to tell CKEDITOR variable where is our external plugin
$this->registerJs("CKEDITOR.plugins.addExternal('pbckcode', '/pbckcode/plugin.js', '');");
$form = ActiveForm::begin();
$this->title = '信息编辑';
$this->params['breadcrumbs'][] = $this->title;
?>




<?php $this->beginBlock('content-header'); ?>
<?= Html::encode($this->title) ?>
<?php $this->endBlock(); ?>

<?= $form->field($model, 'text')->widget(CKEditor::className(), [
		'options' => ['rows' => 6],
		'preset' => 'custom',
		'clientOptions' => [
			'toolbarGroups' => [
				['name' => 'undo'],
				['name' => 'clipboard', 'groups'=> [ 'clipboard', 'undo' ]],
        
			]
		]


]) ?>

    <div class="form-group">
        <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
    </div>

<?php ActiveForm::end(); ?>