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

class LoggerFactory
{
    /**
     * The fully qualified classname of the main Logger class
     *
     * @var string
     */
    protected $classname;

    public function __construct($classname = "\\Monolog\\Logger")
    {
        $this->classname = $classname;
    }

    /**
     * @param string $name The logging channel
     * @param array $handlers Optional stack of handlers, the first one in the array is called first, etc.
     * @param array $processors Optional array of processors
     * @return \Monolog\Logger
     */
    public function create($name, array $handlers = array(), array $processors = array())
    {
        $classname = $this->classname;
        return new $classname($name, $handlers, $processors);
    }
}
