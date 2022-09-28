<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
?>

  

      
      <section class="flush-with-above">
        <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-12">
              <div class="card card-lg">
                <div class="card-body">
                  <form class="wizard">

                    <span class="text-center"> <h6 class="title-decorative mb-2"></h6><h4 class="mb-2">第二步.填写信息</h4></span>
                  </div>


                    <ul class="nav nav-tabs text-center row justify-content-center">
                      <li class="col-3 col-md-2"><a href="#first" class="step-circle step-circle-sm">1</a>
                      </li>
                      <li class="col-3 col-md-2"><a href="#second" class="step-circle step-circle-sm">2</a>
                      </li>
                      <li class="col-3 col-md-2"><a href="#third" class="step-circle step-circle-sm">3</a>
                      </li>
                      <li class="col-3 col-md-2"><a href="#fourth" class="step-circle step-circle-sm">4</a>
                      </li>
                    </ul>

             <div class="tab-content">
                      <div id="first">
                        <div class="row justify-content-around align-items-center">
                          <div class="col-8 col-md-6 col-lg-4 mb-4">
                            <img alt="Image" src="assets/img/graphic-man-box.svg" class="w-100" />
                          </div>
                          <!--end of col-->
                          <div class="col-12 col-md-6 col-lg-5 mb-4">
                            <div>
                              <h6 class="title-decorative mb-2">Step 1.</h6>
                              <h4 class="mb-2">Enter some text</h4>
                              <p>This is especially important so make it memorable</p>
                              <div class="form-group">
                                <input type="text" placeholder="Eg: 'Pumpernickel'" name="name" class="form-control form-control-lg" />
                              </div>
                            </div>
                            <button class="btn btn-success sw-btn-next" type="button">Next Step</button>
                          </div>
                          <!--end of col-->
                        </div>
                        <!--end of row-->
                      </div>
                      <div id="second">
                        <div class="row justify-content-around align-items-center">
                          <div class="col-8 col-md-6 col-lg-4 mb-4">
                            <img alt="Image" src="assets/img/graphic-woman-writing.svg" class="w-100" />
                          </div>
                          <!--end of col-->
                          <div class="col-12 col-md-6 col-lg-5 mb-4">
                            <div>
                              <h6 class="title-decorative mb-2">Step 2.</h6>
                              <h4>Flick some switches</h4>
                              <div>
                                <div class="custom-control custom-switch">
                                  <input type="checkbox" class="custom-control-input" id="box-1">
                                  <label class="custom-control-label" for="box-1">Photography</label>
                                </div>
                              </div>
                              <div>
                                <div class="custom-control custom-switch">
                                  <input type="checkbox" class="custom-control-input" id="box-2">
                                  <label class="custom-control-label" for="box-2">Graphic Design</label>
                                </div>
                              </div>
                              <div>
                                <div class="custom-control custom-switch">
                                  <input type="checkbox" class="custom-control-input" id="box-3">
                                  <label class="custom-control-label" for="box-3">Website Design</label>
                                </div>
                              </div>
                            </div>
                            <button class="btn btn-success sw-btn-next mt-4" type="button">Next Step</button>
                          </div>
                          <!--end of col-->
                        </div>
                        <!--end of row-->
                      </div>
                      <div id="third">
                        <div class="row justify-content-around align-items-center">
                          <div class="col-8 col-md-6 col-lg-4 mb-4">
                            <img alt="Image" src="assets/img/graphic-woman-writing-2.svg" class="w-100" />
                          </div>
                          <!--end of col-->
                          <div class="col-12 col-md-6 col-lg-5 mb-4">
                            <div>
                              <h6 class="title-decorative mb-2">Step 3.</h6>
                              <h4>Select an option</h4>
                              <div>
                                <div class="custom-control custom-radio">
                                  <input type="radio" class="custom-control-input" value="notify-daily" name="notify-frequency" id="notify-daily">
                                  <label class="custom-control-label" for="notify-daily">Daily</label>
                                </div>
                              </div>
                              <div>
                                <div class="custom-control custom-radio">
                                  <input type="radio" class="custom-control-input" value="notify-weekly" name="notify-frequency" checked id="notify-weekly">
                                  <label class="custom-control-label" for="notify-weekly">Weekly</label>
                                </div>
                              </div>
                              <div>
                                <div class="custom-control custom-radio">
                                  <input type="radio" class="custom-control-input" value="notify-monthly" name="notify-frequency" id="notify-monthly">
                                  <label class="custom-control-label" for="notify-monthly">Monthly</label>
                                </div>
                              </div>
                            </div>
                            <button class="btn btn-success sw-btn-next mt-4" type="button">Next Step</button>
                          </div>
                          <!--end of col-->
                        </div>
                        <!--end of row-->
                      </div>
                      <div id="fourth">
                        <div class="row justify-content-around align-items-center">
                          <div class="col-8 col-md-6 col-lg-5 mb-4">
                            <img alt="Image" src="assets/img/graphic-man-computer.svg" class="w-100" />
                          </div>
                          <!--end of col-->
                          <div class="col-12 col-md-6 col-lg-5 mb-4">
                            <div>
                              <h6 class="title-decorative mb-2">Step 4.</h6>
                              <h4 class="mb-2">You're all set</h4>
                              <p>Well done, you've completed the process, just hit the button below to change the world.</p>
                            </div>
                            <button class="btn btn-success btn-lg mt-4" type="submit">Create Greatness</button>
                          </div>
                          <!--end of col-->
                        </div>
                        <!--end of row-->
                      </div>
                    </div>

                  </form>
                </div>
              </div>
            
            </div>
            <!--end of col-->
          </div>
          <!--end of row-->
        </div>
        <!--end of container-->
       
      </section>
      <!--end of section-->
     