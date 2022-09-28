<!-- <?php

require('./Common.php');


$name = 'Material';
$controls = array();

$control = new \stdClass();
$control->type = 'select';
$control->name = 'mode';
$control->options = '[{\'value\':\'opaque\',\'text\':\'opaque\'},{\'value\':\'catout\',\'text\':\'catout\'},{\'value\':\'fade\',\'text\':\'fade\'},{\'value\':\'transparent\',\'text\':\'transparent\'}]';
$control->default = "'catout'";
array_push($controls, $control);


$output = new \stdClass();
$output->name = 'material';
$output->title = 'Material';
$output->socket = 'MaterialSocket';


MackComponent($name, $controls, null, null, $output);
?> -->