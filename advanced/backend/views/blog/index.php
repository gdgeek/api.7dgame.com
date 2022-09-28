<?php

define('WP_USE_THEMES', false);
require_once(\Yii::$app->params['blog']['path']."wp-load.php");

query_posts('showposts=10');
//这个调用最新文章，如果是热门文章的话则改为get_most_viewed("post",10)，前提是你的主题安装了热门文章插件，而且此方法可以接受几乎wp-kit-cn所有代码。
?>


let post =
<?php

$posts_rand = get_posts("include=$id");
$data = $posts_rand[0];
$posts_rand[0]->post_content = apply_filters("wp_content",$posts_rand[0]->post_content);


echo json_encode($posts_rand[0]);

?>;
