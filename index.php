<?php

require_once "vendor/autoload.php";

use BaseDto\Test;


$instance = new Test();

$data = json_decode(file_get_contents(__DIR__.'/test.json'), true);

$instance->fromArray($data);


//var_dump($instance);
var_dump(json_encode($instance));