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

namespace MattyG\MonologCascade\Monolog;

use Monolog\Logger;

class LoggerFactory
{
    /**
     * The fully qualified classname of the main Logger class.
     *
     * @var string
     */
    protected $classname;

    /**
     * @param string $classname The fully qualified classname of the main Logger class.
     */
    public function __construct(string $classname = Logger::class)
    {
        $this->classname = $classname;
    }

    /**
     * @param string $name The logging channel.
     * @param array $handlers Optional stack of handlers, the first one in the array is called first, etc.
     * @param array $processors Optional array of processors.
     * @return Logger
     */
    public function create(string $name, array $handlers = [], array $processors = []): Logger
    {
        $classname = $this->classname;
        return new $classname($name, $handlers, $processors);
    }
}
