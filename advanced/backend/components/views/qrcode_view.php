<?php

use Da\QrCode\QrCode;
?>

<?php
if (\Yii::$app->params['information']['local']) {
    if (isset(\Yii::$app->params['information']['ip'])) {
        ?>
        <hr>
        <b>程序扫码，连接服务器 [<?=\Yii::$app->params['information']['ip']?>]</b><br>
        <?php

        $code = new \stdClass();

        $data = new \stdClass();
        $data->ip = \Yii::$app->params['information']['ip'];
        $code->data = $data;
        $code->veri = 'MrPP.com';
        $qrCode = (new QrCode(json_encode($code)))
            ->setSize(300)
            ->setMargin(5)
            ->useForegroundColor(51, 51, 51);
        ?>

        <?php

        echo '<img src="' . $qrCode->writeDataUri() . '"  class="img-thumbnail">';
        ?>



    <?php
}
} else {

    ?>

    <hr>
<?php

}
?>