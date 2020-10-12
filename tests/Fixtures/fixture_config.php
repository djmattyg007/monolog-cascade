<?php

/**
 * This file is part of the MattyG Monolog Cascade package.
 *
 * (c) Raphael Antonmattei <rantonmattei@theorchard.com>
 * (c) The Orchard
 * (c) Matthew Gamble
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$fixtureArray = [
    'version' => 1,

    'formatters' => [
        'spaced' => [
            'format' => "%datetime% %channel%.%level_name%  %message%\n",
            'includeStacktraces' => true,
        ],
        'dashed' => [
            'format' => "%datetime%-%channel%.%level_name% - %message%\n",
        ],
    ],
    'processors' => array(
        'tag_processor' => array(
            'class' => 'Monolog\Processor\TagProcessor',
        ),
    ),
    'handlers' => array(
        'console' => array(
            'class' => 'Monolog\Handler\StreamHandler',
            'level' => 'DEBUG',
            'formatter' => 'spaced',
            'stream' => 'php://stdout',
        ),
        'info_file_handler' => array(
            'class' => 'Monolog\Handler\StreamHandler',
            'level' => 'INFO',
            'formatter' => 'dashed',
            'stream' => './demo_info.log',
        ),
        'error_file_handler' => array(
            'class' => 'Monolog\Handler\StreamHandler',
            'level' => 'ERROR',
            'formatter' => 'spaced',
            'stream' => './demo_error.log',
        ),
    ),
    'loggers' => array(
        'my_logger' => array(
            'handlers' => array('console', 'info_file_handler'),
        ),
    ),
];
