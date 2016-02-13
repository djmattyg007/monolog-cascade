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
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
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
class Cascade
{
    /**
     * Config class that holds options for all registered loggers.
     * This is optional, you can set up your loggers programmatically.
     *
     * @var Config
     */
    protected static $config = null;

    /**
     * Create a new Logger object and push it to the registry
     *
     * @see Monolog\Logger::__construct
     *
     * @param string $name The logging channel
     * @param HandlerInterface[] $handlers Optional stack of handlers, the first one in the array is called first, etc.
     * @param callable[] $processors Optional array of processors
     * @return Logger A newly created Logger
     */
    public static function createLogger(
        $name,
        array $handlers = array(),
        array $processors = array()
    ) {
        $logger = new Logger($name, $handlers, $processors);
        Registry::addLogger($logger);

        return $logger;
    }

    /**
     * Get a Logger instance by name. Creates a new one if a Logger with the
     * provided name does not exist
     *
     * @param string $name Name of the requested Logger instance
     * @return Logger Requested instance of Logger or new instance
     */
    public static function getLogger($name)
    {
        return Registry::hasLogger($name) ? Registry::getInstance($name) : self::createLogger($name);
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
     * Return the config options
     *
     * @return Config
     */
    public static function getConfig()
    {
        return self::$config;
    }

    /**
     * Load configuration options from a file or a string
     *
     * @param array The array of configuration
     */
    public static function configure(array $config)
    {
        self::$config = new Config($config);
        self::$config->configure();
    }
}
