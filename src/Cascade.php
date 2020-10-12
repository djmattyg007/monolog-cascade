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

namespace MattyG\MonologCascade;

use MattyG\MonologCascade\Config;
use MattyG\MonologCascade\Monolog\LoggerFactory;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Registry;

/**
 * Module class that manages Monolog Logger objects
 *
 * @author Raphael Antonmattei <rantonmattei@theorchard.com>
 * @author Matthew Gamble
 *
 * @see Logger
 * @see Registry
 */
class Cascade extends Registry
{
    /**
     * @var array
     */
    protected static $defaultOptions = [
        "disable_existing_loggers" => true,
    ];

    /**
     * Get a Logger instance by name. Creates a new one if a Logger with the
     * provided name does not exist.
     *
     * @param string $name Name of the requested Logger instance.
     * @return Logger Requested instance of Logger or new instance
     */
    public static function getLogger(string $name): Logger
    {
        return parent::getInstance($name);
    }

    /**
     * Alias of getLogger.
     *
     * @see getLogger
     * @param string $name Name of the requested Logger instance.
     * @return Logger Requested instance of Logger or new instance.
     */
    public static function logger(string $name): Logger
    {
        return self::getLogger($name);
    }

    /**
     * Load configuration options from a file or a string
     *
     * @param array The array of configuration
     * @param LoggerFactory|null $loggerFactory
     */
    public static function configure(array $config, ?LoggerFactory $loggerFactory = null): void
    {
        $options = array_merge(static::$defaultOptions, isset($config["options"]) ? $config["options"] : []);
        unset($config["options"]);

        $configurer = new Config($config, $loggerFactory ?: new LoggerFactory());
        $loggers = $configurer->configure();

        if ($options["disable_existing_loggers"] === true) {
            parent::clear();
        }

        foreach ($loggers as $logger) {
            parent::addLogger($logger);
        }
    }
}
