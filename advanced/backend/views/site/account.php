<?php

use yii\helpers\Url;

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = '创建账号';
$this->params['breadcrumbs'][] = $this->title;


$circle = '<i class="fas fa-check-circle text-green"></i>';
$globe = '<i class="fas fa-globe text-aqua"></i>';



/* @var $this yii\web\View */
?>

<div class="navbar-container">
</div>
<div class="main-container">
  <section class="fullwidth-split">
    <div class="container-fluid">
      <div class="row no-gutters height-100 justify-content-center">
        <div class="col-12 col-lg-6 fullwidth-split-image bg-dark d-flex justify-content-center align-items-center">

          <img alt="Image" src="<?= Url::to("@web/") ?>/pages/assets/img/photo-man-diary.jpg" class="bg-image position-absolute opacity-30" />


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
                  </div>
                </div>
                <!--end of col-->

                <div class="dropdown  notifications-menu" style="position:absolute;width:100px;height：100px;top:10px;left:10px;">
                  <button class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-no-arrow" type="button" id="SidekickButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-globe "></i> <?= Yii::t('app/system', 'English') ?>
                  </button>
                  <div class="dropdown-menu dropdown-menu-sm" style="width:100px" aria-labelledby="SidekickButton">
                    <a class="dropdown-item" href="<?= \yii\helpers\Url::toRoute(["/site/language", "language" => "zh-CN"]) ?>"> <?= \Yii::$app->language == 'zh-CN' ? $circle : $globe ?> 中文(简体) </a>
                    <a class="dropdown-item" href="<?= \yii\helpers\Url::toRoute(["/site/language", "language" => "zh-HK"]) ?>"> <?= \Yii::$app->language == 'zh-HK' ? $circle : $globe ?> 中文(正體)</a>
                    <a class="dropdown-item" href="<?= \yii\helpers\Url::toRoute(["/site/language", "language" => "en-US"]) ?>"> <?= \Yii::$app->language == 'en-US' ? $circle : $globe ?> English</a>
                    <a class="dropdown-item" href="<?= \yii\helpers\Url::toRoute(["/site/language", "language" => "ja-JP"]) ?>"> <?= \Yii::$app->language == 'ja-JP' ? $circle : $globe ?> 日本語 </a>
                  </div>
                </div>
              </div>
              <!--end of col-->
              <div class="col-12 col-sm-8 col-lg-6 fullwidth-split-text">

                <div class="col-12 col-lg-8 align-self-center">
                  <div class="text-center mb-3">

                    <?= common\widgets\bootstrap4\Alert::widget() ?>
                    <h1 class="h2 mb-2">在这里开始</h1>
                    <span>一切的故事的起点，从这个账号诞生。</span>

                  </div>

                  <div class="mb-3">
                    <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                    <?= $form->field($model, 'username')->textInput(['autofocus' => true])->label(Yii::t('app', 'Username')) ?>
                    <?= $form->field($model, 'email')->label(Yii::t('app', 'Email')) ?>
                    <?= $form->field($model, 'password')->passwordInput()->label(Yii::t('app', 'Password')) ?>
                    <?= $form->field($model, 'invitation')->label(Yii::t('app', 'Invitation')) ?>

                    <div class="text-center mt-4">
                      <?= Html::submitButton('创建账号', ['class' => 'btn btn-lg btn-success', 'name' => 'signup-button']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>


                  </div>


                  <div class="text-center">
                    <span class="text-small">你已经有账号了? <a href="<?= Url::toRoute("login") ?>">在这里登录</a></span>
                  </div>
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