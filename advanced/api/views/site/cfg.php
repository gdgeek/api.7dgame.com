<?php


/**
 * Daocontroller
 */

$cfg = new stdClass();
$cfg->urls = [];
array_push($cfg->urls, ["key"=>"api", "value"=>"https://api.mrpp.com/"]);
array_push($cfg->urls, ["key"=>"web", "value"=>"https://mrpp.com/"]);
array_push($cfg->urls, ["key"=>"app", "value"=>"https://app.mrpp.com/"]);
array_push($cfg->urls, ["key"=>"list", "value"=>"https://app.mrpp.com/application#"]);

$cfg->version = ["major" => 1,"minor" => 1,"revision" => 2 ];

