<?php

use yii\helpers\Url;
/* @var $this yii\web\View */
?>

<section class="bg-dark space-sm">
    <div class="main-carousel" data-flickity='{ "cellAlign": "center", "contain": true, "prevNextButtons": false, "pageDots":false, "wrapAround":true, "autoPlay":5000, "imagesLoaded":true }'>


        <?php

        $models = $dataProvider->getModels();
        $count = min(count($models),4);
        for($i =0; $i<$count;++$i){
            ?>


            <div class="carousel-cell col-9 col-md-8 col-lg-5 text-center">
                <a target="_blank" href="<?=Url::toRoute(['/main/blog', 'id'=>$models[$i]->id])?>"  class="d-block mb-3">
                    <img  alt="Image"  src="<?= Url::to("@web/") . $models[$i]->picture?>"  class="img-fluid rounded"  />
                </a>
                <span class="h6"><?=$models[$i]->title?></span>
            </div>

            <?php

        }
        ?>




    </div>
</section>

<section class="space-sm">
    <div class="container">
        <div class="row">
            <div class="col text-center">
                <div class="mb-3">
                    <a  class="btn btn-secondary mb-1"  title="最棒的混合现实/增强现实程序开发平台，帮助您快速生成HoloLens/iPad上面的MR/AR程序，不需要任何编程经验就可以把脑中的想法呈现出来。" href="<?=Url::toRoute("site/index", true)?>" class="btn btn-primary">编程平台</a>
                    <a  class="btn btn-secondary mb-1" title="HoloLens 2 / HoloLens / nreal / MagicLeap One / Action One" disabled="disabled">购买设备(即将开始)</a>

                    <a  class="btn btn-secondary mb-1" target="_blank" href="http://u7gm.com" title="我们是坐落在上海的一家以技术外包服务为主的内资企业。目前主要业务包括，HoloLens 1/2程序设计开发，MagicLeap程序设计开发，协同ARKit和HoloLens多平台等。 希望能有机会和您合作。"  >定制开发</a>

                    <a  class="btn btn-secondary mb-1"  target="_blank" href="<?=Url::to("@web/files/BP@MrPP.pdf", true)?>"  title="先看下商业计划书吧"  >投资我们</a>


                </div>
                <a target="_blank" href="http://u7gm.com"  title="定制开发" style="text-decoration:none;" )">
                <h2>上海游七, 定制开发HoloLens 2 程序</h2>
                <p>联系电话/微信：17521139275 </p>
                </a>
            </div>
            <!--end of col-->
        </div>
        <!--end of row-->
    </div>
    <!--end of container-->
</section>

<section class="space-sm flush-with-above">
    <div class="container">


        <!--end of row-->
        <ul class="row feature-list feature-list-sm">



            <?php

            $models = $dataProvider->getModels();
            foreach($models as $model){
                ?>

                <li class="col-12 col-md-6 col-lg-4">
                    <div class="card">
                        <a target="_blank" href="<?=Url::toRoute(['/main/blog', 'id'=>$model->id])?>"  title="<?=$model->title?>" )">

                            <img class="card-img-top" src="<?=Url::to("@web/") .$model->picture?>"  data-src="<?=$model->picture?>" alt="<?=$model->title?>">

                         </a>
                        <div class="card-body">
                            <h4 class="card-title"><?=$model->title?></h4>
                            <p class="card-text"><?=$model->text?></p>
                        </div>

                    </div>
                </li>


                <?php
                /*
                ?>

                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 ">

                    <div class="thumbnail">
                        <a target="_blank" href="<?=Url::toRoute(['/main/blog', 'id'=>$model->id])?>"  title="<?=$model->title?>" )">
                        <img class="lazy" src="<?=$model->picture?>" width="300" height="150" data-src="<?=$model->picture?>" alt="<?=$model->title?>">
                        </a>
                        <div class="caption">
                            <h3>
                                <a target="_blank" href="<?=Url::toRoute(['/main/blog', 'id'=>$model->id])?>"  title="<?=$model->title?>"  ><?=$model->title?></a>
                            </h3>
                            <p><?=$model->text?></p>
                        </div>
                    </div>
                </div>


                <?php*/

            }
            ?>



        </ul>
        <!--end of row-->
    </div>
    <!--end of container-->
</section>


