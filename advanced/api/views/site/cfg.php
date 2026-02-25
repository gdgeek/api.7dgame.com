<?php


/**
 * Daocontroller
 */

$cfg = new stdClass();
$cfg->urls = [];
array_push($cfg->urls, ["key"=>"api", "value"=>"https://api.xrugc.com/"]);
array_push($cfg->urls, ["key"=>"web", "value"=>"https://xrugc.com/"]);
array_push($cfg->urls, ["key"=>"app", "value"=>"https://app.xrugc.com/"]);
array_push($cfg->urls, ["key"=>"list", "value"=>"https://app.xrugc.com/application#"]);

$cfg->version = ["major" => 1,"minor" => 1,"revision" => 2 ];

