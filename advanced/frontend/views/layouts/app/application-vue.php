<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;


use common\assets\FontAsset;
use frontend\assets\ThemeAsset;
use frontend\assets\BootstrapVueAsset;

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


FontAsset::register($this);


ThemeAsset::register($this);
BootstrapVueAsset::register($this);



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
	<title><?= Yii::$app->params['information']['aka']."-".Yii::$app->params['information']['title']?></title>



  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php $this->registerCsrfMetaTags() ?>
  <?php $this->head() ?>

</head>

<body>

  <?php $this->beginBody() ?>


  <div class="main-container">


    <div class="navbar-container">
      <?php
      ?>
    </div>
    <div class="main-container">
      <section class="fullwidth-split">
        <div class="container-fluid">
          <div class="row no-gutters height-100 justify-content-center">

            <div class="col-12 col-lg-12 fullwidth-split-image bg-dark d-flex justify-content-center align-items-center">

              <img alt="Image" src="<?= Url::to('@web/pages/assets/img/photo-man-diary.jpg') ?>" class="bg-image position-absolute opacity-30" />


              <div class="col-12 text-center pt-5 pb-5">
                <i class="fas fa-user-secret fa-3x"> MrPP.com</i>
                <span class="h2 mb-3"></span>
                <span class="h5 mb-3"> <?= Yii::t('app/site', 'Be a child, Craft a World!') ?></span>
                <?= common\widgets\bootstrap4\Alert::widget() ?>
                <?= $content ?>
                <div class="row">
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


            </div>
            <!--end of col-->


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