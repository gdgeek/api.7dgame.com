<?php


namespace common\modules\editor;

/* @var $this yii\web\View */
use rmrevin\yii\fontawesome\FA;
echo FA::icon('calendar')->inverse(); 


function sort($x,$y){
	return 1;
}


$a = array(1,2);
usort($a, 'common\\modules\\editor\\sort');
?>
<h1>storage/index</h1>

<p>
    You may change the content of this page by modifying
    the file <code><?= __FILE__; ?></code>.
</p>
