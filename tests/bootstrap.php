<?php

date_default_timezone_set('UTC');

require_once __DIR__ . '/../vendor/autoload.php';

$configObject = Common\Config::getInstance();
$path = __DIR__ . '/../config';
$configObject->loadConfig(array("$path/environment.yml", "$path/properties.yml"));

