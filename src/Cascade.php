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
use Monolog\Registry;

/**
 * Module class that manages Monolog Logger objects
 *
 * @author Raphael Antonmattei <rantonmattei@theorchard.com>
 * @author Matthew Gamble
 *
 * @see \Monolog\Logger
 * @see \Monolog\Registry
 */
class Cascade extends Registry
{
    /**
     * @var array
     */
    protected static $defaultOptions = array(
        "disable_existing_loggers" => true,
    );

    /**
     * Get a Logger instance by name. Creates a new one if a Logger with the
     * provided name does not exist
     *
     * @param string $name Name of the requested Logger instance
     * @return Logger Requested instance of Logger or new instance
     */
    public static function getLogger($name)
    {
        return parent::getInstance($name);
    }

    /**
     * Alias of getLogger
     * @see getLogger
     *
     * @param string $name Name of the requested Logger instance
     * @return Logger Requested instance of Logger or new instance
     */
    public static function logger($name)
    {
        return self::getLogger($name);
    }

    /**
     * Load configuration options from a file or a string
     *
     * @param array The array of configuration
     * @param LoggerFactory|null $loggerFactory
     */
    public static function configure(array $config, $loggerFactory = null)
    {
        $options = array_merge(static::$defaultOptions, isset($config["options"]) ? $config["options"] : array());
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
