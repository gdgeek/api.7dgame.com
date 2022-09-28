<?php


use frontend\assets\FlatAsset;
use yii\helpers\Url;
FlatAsset::register($this);
/* @var $this yii\web\View */
?>
  <div class="row demo-tiles">
        <div class="col-xs-6 col-md-6">
          <div class="tile">
            <a class="btn btn-primary btn-large btn-block" href="<?=Url::toRoute(['ar/visit','mode'=>Yii::$app->request->get('mode', null)])?>">
			
            <h3 class="tile-title">Guest</h3>
            <img src="<?=Url::to('@web/images/guest.png')?>" alt="guest" class="tile-image big-illustration">

            <p>以访客身份预览AR/MR程序。</p>
			</a>
          </div>
        </div>

        <div class="col-xs-6 col-md-6 ">
          <div class="tile">
             <a class="btn btn-primary btn-large btn-block" href="<?=Url::toRoute(['ar/login','mode'=>Yii::$app->request->get('mode', null)])?>">
            <h3 class="tile-title">Login</h3>
            <img src="<?=Url::to('@web/images/login.png')?>" alt="guest" class="tile-image big-illustration">
            <p>登录，并展示分享自己做的AR/MR程序。</p>
			</a>
          </div>
        </div>
</div>