 <?php
use yii\helpers\Url;
?>
<div class="main-container">
      <section class="space-sm">
        <div class="container">
          <div class="row">
            <div class="col-12 col-md-12 mb-12 mb-md-12">
              <div class="card card-profile-large text-center">
                <div class="card-header">
                  <img alt="Image" src="<?=Url::to("@web")?>/pages/assets/img/photo-beach.jpg" class="bg-image" />
                </div>
                <div class="card-body">
                  <a href="#">
                    <img alt="Aaron Cunningham" src="<?=Url::to("@web")?>/pages/assets/img/avatar-male-3.jpg" class="avatar avatar-lg" />
                  </a>
                  <div class="my-3">
                    <div class="mb-2">
                       
                      <h5 class="mb-0"><?=Yii::$app->user->isGuest?"访客":Yii::$app->user->identity->username?></h5>
                      <span class="text-muted">开始创造之旅吧！</span>
                    </div>
                  
                  </div>
                  <div>
                    <button class="btn btn-outline-primary"><i class="icon-add-user"></i> Follow</button>
                    <button class="btn btn-outline-primary"><i class="icon-mail"></i>
                    </button>
                  </div>
                </div>
              </div>
              <!-- end card -->
            
            </div>
            <!--end of col-->
           
            <!--end of col-->
          </div>
          <!--end of row-->
        </div>
        <!--end of container-->
      </section>
      
    </div>