<?php

define('WP_USE_THEMES', false);
require_once(\Yii::$app->params['blog']['path']."wp-load.php");

query_posts('showposts=10');
//��������������£�������������µĻ����Ϊget_most_viewed("post",10)��ǰ����������ⰲװ���������²�������Ҵ˷������Խ��ܼ���wp-kit-cn���д��롣
?>


let post =
<?php

$posts_rand = get_posts("include=$id");
$data = $posts_rand[0];
$posts_rand[0]->post_content = apply_filters("wp_content",$posts_rand[0]->post_content);


echo json_encode($posts_rand[0]);

?>;
