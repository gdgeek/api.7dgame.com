<?php

use yii\helpers\Url;
?>


<div id="app" class="container">

    <div class="row justify-content-center">
        <b-col cols="12">

            <b-card>
                <b-card no-body>
                    <b-card-header header-tag="nav" class=" bg-secondary justify-content-between ">
                        <b-nav card-header pills align="right">

                            <b-button-group>
                                <b-button variant="success">登录</b-button>
                                <b-link href="telnet://execute?input=<?= urlencode(json_encode(new class
                                                                        {
                                                                            public $command = "exit";
                                                                        }, JSON_UNESCAPED_SLASHES)) ?>" class="btn btn-success">退出</b-link>

                            </b-button-group>
                        </b-nav>


                    </b-card-header>

                    <section class="flush-with-above space-0">
                        <div class="bg-white">
                            <b-container>
                                <b-row>
                                    <b-col>
                                        <div>
                                            <b-tabs content-class="mt-3">
                                                <b-tab title="推荐" active>

                                                    <b-list-group class=" list-group-flush">



                                                        <?php

                                                        foreach ($projects as  $project) {


                                                            $input = new StdClass();
                                                            $input->projectId = $project->id;
                                                            $input->title = $project->title;
                                                            $input->introduce = $project->introduce;
                                                            $json = json_encode($input, JSON_UNESCAPED_SLASHES);
                                                            $value = urlencode($json);
                                                            $url = 'telnet://project?input=' . $value;

                                                            if (Yii::$app->request->get('mode', null) == 'pc') {
                                                                $url = '#';
                                                            }
                                                        ?>
                                                            <b-list-group-item>
                                                                <b-link onclick='load_project(<?= json_encode($json) ?>);' href="<?= $url ?>" class="mr-4">
                                                                    <b-media tag="li">

                                                                        <template #aside>
                                                                            <b-img class="tile-image " style='' src="<?= Yii::$app->params['identicon']->getImageDataUri($project->id . Yii::$app->user->id, 64); ?>">
                                                                            </b-img>
                                                                        </template>


                                                                        <b-media-body>


                                                                            <h4 class="mt-0 mb-1" style="text-align:left"><?= $project->title ?></h4>
                                                                            <p class="mb-0" style="text-align:left">
                                                                                <?= $project->introduce ?>
                                                                            </p>




                                                                        </b-media-body>
                                                                    </b-media>
                                                                </b-link>
                                                            </b-list-group-item>
                                                        <?php

                                                        }
                                                        ?>







                                                    </b-list-group>
                                                </b-tab>
                                                <b-tab title="我创造的">
                                                    <p>I'm the second tab</p>
                                                </b-tab>

                                            </b-tabs>
                                        </div>
                                    </b-col>
                                    <!--end of col-->
                                </b-row>
                                <!--end of row-->
                            </b-container>
                            <!--end of container-->
                        </div>
                    </section>


                </b-card>

            </b-card>



        </b-col>
        <!--end of col-->
    </div>
    <!--end of row-->
</div>


<script>
    new Vue({
        el: '#app',
        data() {
            return {
                projects: null
            }
        },
        mounted() {
          
        }
    })
</script>