<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use common\widgets\Alert;

use frontend\assets\FlatAsset;

AppAsset::register($this);
FlatAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->params['information']['aka'] ,
        'brandUrl' => '#',
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    $menuItems = [
    ];
	
    if (Yii::$app->user->isGuest) {
		$menuItems[] = ['label' => '访客', 'url' => ['/ar/visit','mode'=>Yii::$app->request->get('mode', null)]];
        $menuItems[] = ['label' => '登陆', 'url' => ['/ar/login','mode'=>Yii::$app->request->get('mode', null)]];
    } else {
        $menuItems[] = '<li>'
            . Html::beginForm(['/ar/logout','mode'=>Yii::$app->request->get('mode', null)], 'post')
            . Html::submitButton(
                '登出 (' . Yii::$app->user->identity->username . ')',
                ['class' => 'btn btn-link logout']
            )
            . Html::endForm()
            . '</li>';
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

    <div class="container">
      
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy;<?=Yii::$app->params['information']['title']?>  <?= date('Y') ?></p>

        <p class="pull-right"><?=Yii::$app->params['information']['company']"?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
