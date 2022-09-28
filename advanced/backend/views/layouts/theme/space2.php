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


$language = Yii::$app->request->get('language');
if (isset($language) && Yii::$app->session['language'] != $language) {
  $session = Yii::$app->session;
  $session->open();
  Yii::$app->session['language'] = $language;
  Yii::$app->controller->redirect(Url::current(['language' => null]));
}


$cssString = ".help-block-error{color:#a00000;}";
$this->registerCss($cssString);




$circle = '<i class="fas fa-check-circle text-green"></i>';
$globe = '<i class="fas fa-globe text-aqua"></i>';

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
  <title><?= Html::encode($this->title) . "-" . Yii::$app->params['information']['title'] ?></title>



  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php $this->registerCsrfMetaTags() ?>
  <?php $this->head() ?>

</head>

<body>

  <?php $this->beginBody() ?>


  <div class="main-container">


    <div class="navbar-container">
    </div>
    <div class="main-container">
      <section class="fullwidth-split">
        <div class="container-fluid">
          <div class="row no-gutters height-100 justify-content-center">
            <div class="col-12 col-lg-6 fullwidth-split-image bg-dark d-flex justify-content-center align-items-center">

              <img alt="Image" src="<?= Url::to("@web/") ?>pages/assets/img/photo-man-diary.jpg" class="bg-image position-absolute opacity-30" />


              <div class="col-12 col-sm-8 col-lg-9 text-center pt-5 pb-5">
                <i class="fas fa-user-secret fa-3x"> MrPP.com</i>
                <span class="h2 mb-3"></span>
                <span class="h5 mb-3"> <?= Yii::t('app/site', 'Be a child, Craft a World!') ?></span>
                <div class="card text-left">
                  <div class="card-body">
                    <div class="media">


                      <img src="<?= Url::to('@web/public/image/qrcode.jpg') ?>" style="width:200px">

                      <div class="media-body">
                        <p class="h5 mb-3" style="text-align:center">
                          微信扫码，得邀请码
                        </p>
                        <small class="h6 mb-2" ">扫描左侧二维码，在公众号内得到邀请码，然后就可以注册到崭新的时空中，我们一起创造全息的世界。</small>
                      </div>
                    </div>
                  </div>
                </div>
                <div class=" row">
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
                          <!--end of col-->
                      </div>
                    </div>
                    <!--end of col-->

                    <div class="dropdown  notifications-menu" style="position:absolute;width:100px;height：100px;top:10px;left:10px;">
                      <button class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-no-arrow" type="button" id="SidekickButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-globe "></i> <?= Yii::t('app/system', 'English') ?>
                      </button>
                      <div class="dropdown-menu dropdown-menu-sm" style="width:100px" aria-labelledby="SidekickButton">
                        <a class="dropdown-item" href="<?= \yii\helpers\Url::current(["language" => "zh-CN"]) ?>"> <?= \Yii::$app->language == 'zh-CN' ? $circle : $globe ?> 中文(简体) </a>
                        <a class="dropdown-item" href="<?= \yii\helpers\Url::current(["language" => "zh-HK"]) ?>"> <?= \Yii::$app->language == 'zh-HK' ? $circle : $globe ?> 中文(正體)</a>
                        <a class="dropdown-item" href="<?= \yii\helpers\Url::current(["language" => "en-US"]) ?>"> <?= \Yii::$app->language == 'en-US' ? $circle : $globe ?> English</a>
                        <a class="dropdown-item" href="<?= \yii\helpers\Url::current(["language" => "ja-JP"]) ?>"> <?= \Yii::$app->language == 'ja-JP' ? $circle : $globe ?> 日本語 </a>
                      </div>
                    </div>
                  </div>
                  <!--end of col-->
                  <div class="col-12 col-sm-8 col-lg-6 fullwidth-split-text">

                    <div class="col-12 col-lg-8 align-self-center">
                      <?= common\widgets\bootstrap4\Alert::widget() ?>
                      <?= $content ?>
                    </div>

                    <!--end of col-->
                  </div>

                  <!--end of col-->
                </div>

                <!--end of row-->
              </div>

              <!--end of container-->
      </section>
      <!--end of section-->
    </div>

    <!--end of section-->


  </div>


  <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>