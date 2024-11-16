<?php

require_once "vendor/autoload.php";

use BaseDto\Test;
use BaseDto\Generator;

//$generator = new Generator();
//$generator->generate('src/Gen/InitRequest.yaml');
//die;
$instance = new \BaseDto\Gen\InitRequest();

$data = json_decode(file_get_contents(__DIR__.'/test.json'), true);

$instance->fromArray($data);

$instance->setAccount('asd123')
    ->setSignature('asdasdsa');

var_dump($instance);
var_dump(json_encode($instance));
