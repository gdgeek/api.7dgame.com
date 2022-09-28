<?php

use yii\helpers\Html;
/* @var $this \yii\web\View */
/* @var $content string */

if (Yii::$app->controller->action->id === 'login') {
    /**
     * Do not use this code in your template. Remove it. 
     * Instead, use the code  $this->layout = '//main-login'; in your controller.
     */
    echo $this->render(
        'main-login',
        ['content' => $content]
    );
} else {

    if (class_exists('backend\assets\AppAsset')) {
        //## backend\assets\AppAsset::register($this);
    } else {
        //##  app\assets\AppAsset::register($this);
    }

    dmstr\web\AdminLteAsset::register($this);

    $directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');

    $this->registerMetaTag(['name' => 'keywords', 'content' => 'HoloLens, Magic Leap, Action One, iPad, VR, AR, MR, 混合现实, 虚拟现实, 增强现实, 微软混合现实合作伙伴认证计划, MRPP, Mixed Reality PartnerProgram']);
    $this->registerMetaTag(['name' => 'description', 'content' => '最棒的混合现实/增强现实程序编程平台，帮助您快速生成HoloLens/iPad上面的MR/AR程序，不需要任何编程经验就可以把脑中的想法呈现出来。']);
    $this->registerMetaTag(['name' => 'author', 'content' => Yii::$app->params['information']['company']]);


?>
    <?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">

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

        <meta charset="<?= Yii::$app->charset ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Yii::$app->params['information']['aka'] . "-" . Yii::$app->params['information']['title'] ?></title>
        <?php $this->head() ?>
    </head>

    <body class="hold-transition skin-blue sidebar-mini">

        <?php $this->beginBody() ?>
        <div class="wrapper">

            <?= $this->render(
                'header.php',
                ['directoryAsset' => $directoryAsset]
            ) ?>

            <?= $this->render(
                'left.php',
                ['directoryAsset' => $directoryAsset]
            )
            ?>

            <?= $this->render(
                'content.php',
                ['content' => $content, 'directoryAsset' => $directoryAsset]
            ) ?>

        </div>

        <?php $this->endBody() ?>
    </body>

    </html>
    <?php $this->endPage() ?>
<?php } ?>