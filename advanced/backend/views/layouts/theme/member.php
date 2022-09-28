<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use backend\assets\ThemeAsset;
use backend\assets\FontAsset;
use common\widgets\Alert;
use yii\helpers\Url;


ThemeAsset::register($this);
FontAsset::register($this);

$this->registerMetaTag(['name' => 'keywords', 'content' => 'HoloLens, Magic Leap, Action One, iPad, VR, AR, MR, 混合现实, 虚拟现实, 增强现实']);
$this->registerMetaTag(['name' => 'description', 'content' => '最棒的混合现实/增强现实程序开发平台，帮助您快速生成HoloLens/iPad上面的MR/AR程序，不需要任何编程经验就可以把脑中的想法呈现出来。']);
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


    <meta charset="<?= Yii::$app->charset ?>">
    <title><?= Html::encode($this->title) ?></title>
    <meta name="description" content="A robust suite of app and landing page templates by Medium Rare">
    <link href="https://fonts.googleapis.com/css?family=Rubik:300,400,400i,500" rel="stylesheet">


    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <?php $this->head() ?>

</head>

<body>

    <?php $this->beginBody() ?>


    <div class="navbar-container">
        <div class="bg-dark navbar-dark" data-sticky="top">
            <div class="container">
                <nav class="navbar navbar-expand-lg">
                    <?= Html::a('<i class="fas fa-user-secret "> ' . Yii::$app->name . '</i>',  Yii::$app->homeUrl, ['class' => 'navbar-brand']) ?>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="icon-menu h4"></i>
                    </button>
                    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
                        <ul class="navbar-nav">

                        </ul>

                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <?= Html::a('混合现实编程平台', ['/site/index'], ['class' => 'nav-link']) ?>

                            </li>

                            <?php
                            if (Yii::$app->user->isGuest) {
                                echo Html::a('注册', ['/site/signup'], ['class' => 'nav-link']);
                                echo Html::a('登录', ['/site/login'], ['class' => 'nav-link']);
                            } else {
                                echo '<li>'
                                    . Html::beginForm(['/site/logout'], 'post')
                                    . Html::submitButton(
                                        '登出 (' . Yii::$app->user->identity->username . ')',
                                        ['class' => 'btn btn-link logout']
                                    )
                                    . Html::endForm()
                                    . '</li>';
                            }


                            ?>

                        </ul>

                    </div>
                    <!--end nav collapse-->
                </nav>
            </div>
            <!--end of container-->
        </div>
    </div>


    <div class="main-container">


        <?= $content ?>

        <!--end of section-->

        <!--end of section-->
        <footer class="footer-short">
            <div class="container">
                <hr>

                <!--end of row-->
                <div class="row">

                    <div class="site-index">

                    </div>

                    <div class="col">
                        <small>&copy; <?= date("Y") ?> <?= Yii::$app->params['information']['company'] ?></small>
                        <?php
                        if (!Yii::$app->params['information']['local']) {
                        ?>
                            <small><a target="_blank" href="http://www.beian.miit.gov.cn">备案号：沪ICP备17013833号</a></small>
                        <?php
                        }
                        ?>
                    </div>
                    <!--end of col-->
                </div>
                <!--end of row-->
            </div>
            <!--end of container-->
        </footer>
    </div>


    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>