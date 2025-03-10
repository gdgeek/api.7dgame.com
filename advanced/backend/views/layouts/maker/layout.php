<?php
use common\widgets\Alert;
use rmrevin\yii\fontawesome\FA;
use yii\helpers\Html;
/* @var $this \yii\web\View */
/* @var $content string */

dmstr\web\AdminLteAsset::register($this);

$this->registerMetaTag(['name' => 'keywords', 'content' => 'HoloLens, Magic Leap, Action One, iPad, VR, AR, MR, 混合现实, 虚拟现实, 增强现实']);
$this->registerMetaTag(['name' => 'description', 'content' => '最棒的混合现实/增强现实程序开发平台，帮助您快速生成HoloLens/iPad上面的MR/AR程序，不需要任何编程经验就可以把脑中的想法呈现出来。']);
$this->registerMetaTag(['name' => 'author', 'content' => Yii::$app->params['information']['company']]);

?>
<?php $this->beginPage()?>
<!DOCTYPE html>
<html lang="<?=Yii::$app->language?>">
<head>
    <script>
        var _hmt = _hmt || [];
        (function() {
            var hm = document.createElement("script");
            hm.src = "https://hm.baidu.com/hm.js?da43806af71158eadcd62ba8af9e3281";
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(hm, s);
        })();
    </script>

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-P3D4HX8');</script>
    <!-- End Google Tag Manager -->
    <meta charset="<?=Yii::$app->charset?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?=Html::csrfMetaTags()?>
	<title><?=Yii::$app->params['information']['aka'] . "-" . Yii::$app->params['information']['title']?></title>

    <?php $this->head()?>
</head>
<body class="login-page">
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-P3D4HX8"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<?php $this->beginBody()?>

<div class="login-box">
    <div class="login-logo">
        <a href="#"><b>
	<?php
echo $this->blocks['content-header'];
?>
		</b></a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <div class="login-box-msg"><?=Yii::$app->params['information']['sub-title']?></div>
		<div class="login-box-msg"><?=Alert::widget()?></div>	<?php
echo $this->blocks['content-main'];
?>
	</div>
	<?php
if (isset($this->blocks['content-footer'])) {
    ?>

		<br/>

		 	<div class="login-box-body">
			<?=$this->blocks['content-footer']?>

			</div>

	<?php
}
?>
	<br/>


<?php

echo 123;
if (!Yii::$app->params['information']['local']) {

    ?>
<div class="login-box-body">
		<div class="site-index">
			<?php
echo Html::a(FA::icon('flag') . ' 当前进行工作', ['#collapseThree'], ['data-toggle' => 'collapse', 'data-parent' => '#accordion']);
    ?>

		</div>

		<div id="collapseThree" class="panel-collapse collapse">
			<div class="panel-body">
				<li>公共模型库</li>
				<li>用md5做文件名</li>
			</div>
		</div>
		<hr>
		<div class="site-index">
			<?php
echo Html::a(FA::icon('money') . ' 平台商业计划书', ['/files/BP@MrPP.pdf'], ['target' => '_blank']);
    ?>
		</div>
	</div>
<?php
}
?>

 </div>





<?=$content?>


<?php $this->endBody()?>
</body>
</html>
<?php $this->endPage()?>
