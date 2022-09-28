<?php

use yii\helpers\Url;

?>
    <div class="container">
        <div class="row justify-content-center">
        <div class="col-12">
            <div class="card card-lg text-center">
            <div class="card-body">
                <h1 class="h5">Yii::$app->params['information']['title']</h1>
                <p class="text-left">
                最棒的混合现实/增强现实程序开发平台，帮助您快速生成HoloLens/iPad上面的MR/AR程序，不需要任何编程经验就可以把脑中的想法呈现出来。
                </p>
                <a href="<?=Url::toRoute("guest")?>" class="btn btn-lg btn-primary">访客参观</a>
                <a href="<?=Url::toRoute("login")?>" class="btn btn-lg btn-primary">账户登录</a>
            </div>
            </div>
        </div>
        <!--end of col-->
        </div>
        <!--end of row-->
    </div>