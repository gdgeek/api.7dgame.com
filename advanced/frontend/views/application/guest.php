<?php

use yii\helpers\Url;

?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">


            <div class="card card-lg text-center">
                <div class="card-body">
                    <div class="card card-sm">
                        <div class="card-header d-flex bg-secondary justify-content-between align-items-center">
                            <div>
                                <h6>选择场景</h6>
                            </div>

                            <div class="col-auto text-right">
                                <div class="btn-group" role="group" aria-label="...">
                                    <a href="#" class="btn btn-success">登录</a>
                                    <a href="#" class="btn btn-success">退出</a>
                                </div>
                            </div>

                        </div>
                        <section class="flush-with-above space-0">
                            <div class="bg-white">
                                <div class="container">
                                    <div class="row">
                                        <div class="col">
                                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" id="overview-tab" data-toggle="tab" href="#general" role="tab" aria-selected="true">General</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="billing-tab" data-toggle="tab" href="#billing" role="tab" aria-selected="false">Billing</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="security-tab" data-toggle="tab" href="#security" role="tab" aria-selected="false">Security</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="notifications-tab" data-toggle="tab" href="#notifications" role="tab" aria-selected="false">Notifications</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <!--end of col-->
                                    </div>
                                    <!--end of row-->
                                </div>
                                <!--end of container-->
                            </div>
                        </section>
                        <section class="flush-with-above height-10 d-block">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="general" role="tabpanel">
                                    <div class="container">
                                        <ul class="list-group list-group-flush">

                                            <li class="list-group-item">
                                                <div class="media align-items-center">
                                                    <a href="#" class="mr-4">
                                                        <img alt="Image" src="../pages/assets/img/graphic-product-sidekick-thumb.jpg" class="rounded avatar avatar-lg" />
                                                    </a>
                                                    <div class="media-body">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <div style="text-align:left">
                                                                <a href="#" class="mb-1">
                                                                    <h4>Sidekick</h4>
                                                                </a>
                                                                <span>这个事情你就别管了，我看看我怎么处理
                                                            </div>

                                                        </div>
                                                    </div>
                                               </div>
                                            </li>
                                        </ul>
                                    </div>
                                    <!--end of container-->
                                </div>
                                <!--end of tab pane-->
                                <div class="tab-pane fade" id="billing" role="tabpanel">
                                    <div class="container">

                                    </div>
                                    <!--end of container-->
                                </div>
                                <!--end of tab pane-->
                                <div class="tab-pane fade" id="notifications" role="tabpanel">
                                    <div class="container">

                                    </div>
                                    <!--end of container-->
                                </div>
                            </div>
                            <!--end of tabs content-->
                        </section>


                    </div>
                </div>
            </div>
        </div>
        <!--end of col-->
    </div>
    <!--end of row-->
</div>