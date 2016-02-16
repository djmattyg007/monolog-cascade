<?php

require_once(dirname(__DIR__) . "/vendor/autoload.php");

use MattyG\MonologCascade\Cascade;

// For these to work you will need php-redis and raven/raven

// You will want to update this file with a valid dsn
$loggerConfig = file_get_contents(__DIR__ . "/dependency_config.json");
$loggerConfigArray = json_decode($loggerConfig, true);

Cascade::configure($loggerConfigArray);
Cascade::getLogger('dependency')->info('Well, that works!');
Cascade::getLogger('dependency')->error('Maybe not...');
