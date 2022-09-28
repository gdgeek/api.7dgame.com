<?php
$data = new stdClass();
$data->logic = $project->logic;
$data->configure = json_decode($project->configure);
$data->title = $project->title;
$data->introduce = $project->introduce;
echo json_encode($data, JSON_UNESCAPED_SLASHES);
?>

