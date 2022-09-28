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

$code->projectId = $id;
$code->veri = 'MrPP.com';
$qrCode = (new QrCode(json_encode($code)))
    ->setSize(250)
    ->setMargin(5)
    ->useForegroundColor(51, 153, 255);



?> 

<?php 
// or even as data:uri url
echo '<img src="' . $qrCode->writeDataUri() . '">';
?>