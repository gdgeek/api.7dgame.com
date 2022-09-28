<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use common\widgets\Alert;
use yii\helpers\Url;


AppAsset::register($this);
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

    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-P3D4HX8');
    </script>
    <!-- End Google Tag Manager -->


    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-P3D4HX8" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php $this->beginBody() ?>

    <div class="wrap">
        <?php
        NavBar::begin([
            'brandLabel' => Yii::$app->name,
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar-inverse navbar-fixed-top',
            ],
        ]);
        $menuItems = [
            ['label' => Yii::$app->params['information']['title'], 'url' => ['/site/index']],
        ];
        if (Yii::$app->user->isGuest) {
            $menuItems[] = ['label' => '注册', 'url' => ['/site/signup']];
            $menuItems[] = ['label' => '登录', 'url' => ['/site/login']];
        } else {
            $menuItems[] = '<li>'
                . Html::beginForm(['/site/logout'], 'post')
                . Html::submitButton(
                    '登出 (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>';
        }
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-right'],
            'items' => $menuItems,
        ]);
        NavBar::end();
        ?>

        <div class="container">
            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>

            <?= Alert::widget() ?>

            <div class="row demo-navigation">


            </div> <!-- /demo-navigation -->
            <div class="btn-group btn-group-justified visible-sm-block visible-xs-block" role="group" aria-label="...">
                <div class="btn-group" role="group">
                    <a type="button" title="最棒的混合现实/增强现实程序开发平台，帮助您快速生成HoloLens/iPad上面的MR/AR程序，不需要任何编程经验就可以把脑中的想法呈现出来。" href="<?= Url::toRoute("site/index", true) ?>" class="btn btn-primary">编程平台</a>
                </div>
                <div class="btn-group" role="group">
                    <a type="button" class="btn btn-default " title="HoloLens 2 / HoloLens / nreal / MagicLeap One / Action One" disabled="disabled">购买设备(即将开始)</a>
                </div>
                <div class="btn-group" role="group">
                    <a target="_blank" href="http://u7gm.com" title="我们是坐落在上海的一家以技术外包服务为主的内资企业。目前主要业务包括，HoloLens 1/2程序设计开发，MagicLeap程序设计开发，协同ARKit和HoloLens多平台等。 希望能有机会和您合作。" type="button" class="btn btn-default">定制开发</a>
                </div>
                <div class="btn-group" role="group">
                    <a target="_blank" href="<?= Url::to("@web/files/BP@MrPP.pdf", true) ?>" title="先看下商业计划书吧" type="button" class="btn btn-default">投资我们</a>
                </div>
            </div>

            <div class="row demo-tiles row-eq-height " style="height:100%;">


                <div class="container col-xs-12 col-sm-12 col-md-8 " style="height:auto; padding: 10px 10px 0px 10px;">
                    <?php if (isset($this->blocks['content-jumbotron'])) { ?>
                        <?= $this->blocks['content-jumbotron'] ?>
                    <?php } ?>
                </div>
                <div class="container  col-sm-4 hidden-sm hidden-xs" style=" padding: 10px 10px 10px 10px;">

                    <div class="list-group ">
                        <a href="<?= Url::toRoute("site/index", true) ?>" class="list-group-item active">
                            <h4 class="list-group-item-heading">登录混合现实编程平台</h4>
                            <p class="list-group-item-text">最棒的混合现实/增强现实程序开发平台，帮助您快速生成HoloLens/iPad上面的MR/AR程序，不需要任何编程经验就可以把脑中的想法呈现出来。</p>
                        </a>

                        <a href="#" class="list-group-item " disabled="disabled">
                            <h4 class="list-group-item-heading">购买设备（即将开始）</h4>
                            <p class="list-group-item-text">HoloLens 2 / HoloLens / nreal / MagicLeap One / Action One</p>
                        </a>
                        <a target="_blank" href="http://u7gm.com" class="list-group-item ">
                            <h4 class="list-group-item-heading">定制开发</h4>
                            <p class="list-group-item-text">我们是坐落在上海的一家以技术外包服务为主的内资企业。目前主要业务包括，HoloLens 1/2程序设计开发，MagicLeap程序设计开发，协同ARKit和HoloLens多平台等。 希望能有机会和您合作。</p>
                        </a>
                        <a target="_blank" href="<?= Url::to("@web/files/BP@MrPP.pdf", true) ?>" class="list-group-item ">
                            <h4 class="list-group-item-heading">投资我们</h4>
                            <p class="list-group-item-text">先看下商业计划书吧</p>
                        </a>


                    </div>

                </div>


            </div>

            <?= $content ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p class="pull-left">&copy; <?= date("Y") ?> <?= Yii::$app->params['information']['company'] ?></p>
            <?php
            if (!Yii::$app->params['information']['local']) {
            ?>
                <p class="pull-right"><a target="_blank" href="http://www.miitbeian.gov.cn">备案号：沪ICP备17013833号</a></p>
            <?php
            }
            ?>
        </div>
    </footer>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>