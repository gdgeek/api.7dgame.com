<?php

/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\FontAsset;
use backend\assets\ThemeAsset;

$cssString = ".help-block-error{color:#a00000;}";
$this->registerCss($cssString);

ThemeAsset::register($this);
FontAsset::register($this);

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


    <meta charset="<?=Yii::$app->charset?>">
	<title><?=Yii::$app->params['information']['aka'] . "-" . Yii::$app->params['information']['title']?></title>

    <meta name="description" content="A robust suite of app and landing page templates by Medium Rare">
    <link href="https://fonts.googleapis.com/css?family=Rubik:300,400,400i,500" rel="stylesheet">


    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags()?>
    <?php $this->head()?>

</head>

<body>

<?php $this->beginBody()?>


<div class="main-container">

    <?=$content?>

    <!--end of section-->


</div>


<?php $this->endBody()?>
</body>
</html>
<?php $this->endPage()?>

