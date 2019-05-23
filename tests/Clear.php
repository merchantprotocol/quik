<?php 
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

$Clear = new \Quik\Clear();
$response = $Clear->dirs();

echo $response->output;