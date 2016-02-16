<?php

require_once(dirname(__DIR__) . "/vendor/autoload.php");

use MattyG\MonologCascade\Cascade;

$loggerConfig = file_get_contents(__DIR__ . "/logger_config.json");
$loggerConfigArray = json_decode($loggerConfig, true);

Cascade::configure($loggerConfigArray);
Cascade::getLogger('loggerA')->info('Well, that works!');
Cascade::getLogger('loggerB')->error('Maybe not...');

// This should log into 2 different log files depending on the level: 'example_info.log' and 'example_error.log'
