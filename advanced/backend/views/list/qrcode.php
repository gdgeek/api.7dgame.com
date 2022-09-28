<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel common\models\ProjectSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'QRCode';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php

use Da\QrCode\QrCode;

$code =  new \stdClass();

$data = new \stdClass();
$data->id = $id;
$code->data = $data;
$code->veri = 'MrPP.com';
$qrcode = (new QrCode(json_encode($code)))
    ->setSize(300)
    ->setMargin(5)
    ->useForegroundColor(51, 153, 255);
?> 

<?php
echo '<img src="' . $qrcode->writeDataUri() . '">';
?>