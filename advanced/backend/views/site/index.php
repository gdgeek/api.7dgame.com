<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\SignupForm */

use backend\components\QRCodeWidget;
use common\widgets\Alert;
use yii\helpers\Url;

rmrevin\yii\fontawesome\AssetBundle::register($this);

$this->title = Yii::$app->params['information']['aka'];
$this->params['breadcrumbs'][] = $this->title;
//echo json_encode(\Yii::$app->params);
?>


<?php $this->beginBlock('content-header');?>
<?=Yii::$app->params['information']['title']?>
<?php $this->endBlock();?>

<?php $this->beginBlock('content-main');?>
<?php if (!Yii::$app->user->isGuest) {
    ?>
	<h1>欢迎!&nbsp;&nbsp;&nbsp;<B><?=Yii::$app->user->identity->username?></B></h1>
	<p class="lead">开启您的创造世界之旅吧。</p>
	<p class="lead"><?=Alert::widget()?></p>
	<p><a class="btn btn-lg btn-success" href=" <?=Url::toRoute('document/index')?>">进入</a></p>

	<a href="<?=Url::toRoute('site/logout')?>">登出账号</a><br>


<?php
} else {
    ?>
	<h1>欢迎!</h1><br>
	<h4>准备好出发了么？ </h4>
	<p class="lead"><?=Alert::widget()?></p>
	<a href="<?=Url::toRoute('site/login')?>">登陆账号</a><br>
	<a href="<?=Url::toRoute('site/signup')?>">注册用户</a><br>

	<?=QRCodeWidget::widget([])?>


<?php
}

?>


<?php $this->endBlock();?>