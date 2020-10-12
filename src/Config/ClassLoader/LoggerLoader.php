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

namespace MattyG\MonologCascade\Config\ClassLoader;

use InvalidArgumentException;
use MattyG\MonologCascade\Cascade;
use MattyG\MonologCascade\Monolog\LoggerFactory;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;

/**
 * Logger Loader. Instantiate a Logger and set passed in handlers and processors if any
 *
 * @author Raphael Antonmattei <rantonmattei@theorchard.com>
 * @author Matthew Gamble
 */
class LoggerLoader
{
    /**
     * @var LoggerFactory
     */
    protected $loggerFactory;

    /**
     * Array of handlers.
     *
     * @var HandlerInterface[]
     */
    protected $handlers = [];

    /**
     * Array of processors.
     *
     * @var callable[]
     */
    protected $processors = [];

    /**
     * @param LoggerFactory $loggerFactory
     * @param HandlerInterface[] $handlers Array of Monolog handlers.
     * @param callable[] $processors Array of processors.
     */
    public function __construct(
        LoggerFactory $loggerFactory,
        array $handlers = [],
        array $processors = []
    ) {
        $this->loggerFactory = $loggerFactory;
        $this->handlers = $handlers;
        $this->processors = $processors;
    }

    /**
     * Resolve handlers for that Logger (if any provided) against an array of previously set
     * up handlers. Returns an array of valid handlers.
     *
     * @param array $loggerOptions An array of logger options.
     * @param HandlerInterface[] $handlers Available Handlers to resolve against.
     * @return HandlerInterface[] Array of Monolog handlers.
     * @throws InvalidArgumentException If a requested handler is not available in $handlers.
     */
    public function resolveHandlers(array $loggerOptions, array $handlers): array
    {
        $handlerArray = [];

        if (isset($loggerOptions["handlers"])) {
            // If handlers have been specified and, they do exist in the provided handlers array
            // We return an array of handler objects
            foreach ($loggerOptions["handlers"] as $handlerId) {
                if (isset($handlers[$handlerId])) {
                    $handlerArray[] = $handlers[$handlerId];
                } else {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Cannot add handler "%s" to the logger "%s". Handler not found.',
                            $handlerId,
                            $loggerOptions["name"]
                        )
                    );
                }
            }
        }

        // If nothing is set there is nothing to resolve, Handlers will be Monolog's default.
        return $handlerArray;
    }

    /**
     * Resolve processors for that Logger (if any provided) against an array of previously set
     * up processors.
     *
     * @param array $loggerOptions An array of logger options.
     * @param callable[] $processors Available Processors to resolve against.
     * @return callable[] Array of Monolog processors.
     * @throws InvalidArgumentException If a requested processor is not available in $processors.
     */
    public function resolveProcessors(array $loggerOptions, $processors): array
    {
        $processorArray = [];

        if (isset($loggerOptions["processors"])) {
            // If processors have been specified and, they do exist in the provided processors array
            // We return an array of processor objects
            foreach ($loggerOptions["processors"] as $processorId) {
                if (isset($processors[$processorId])) {
                    $processorArray[] = $processors[$processorId];
                } else {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Cannot add processor "%s" to the logger "%s". Processor not found.',
                            $processorId,
                            $loggerOptions["name"]
                        )
                    );
                }
            }
        }

        // If nothing is set there is nothing to resolve, Processors will be Monolog's default
        return $processorArray;
    }

    /**
     * Add processors to the Logger.
     *
     * @param Logger $logger
     * @param callable[] Array of Monolog processors.
     */
    protected function addProcessors(Logger $logger, array $processors): void
    {
        // We need to reverse the array because Monolog "pushes" processors to top of the stack
        foreach (array_reverse($processors) as $processor) {
            $logger->pushProcessor($processor);
        }
    }

    /**
     * Return the instantiated Logger object based on its name.
     *
     * @param string $name The name of the logging channel being created.
     * @param array $loggerOptions
     * @return Logger Logger object.
     */
    public function load($name, array $loggerOptions = []): Logger
    {
        $logger = $this->loggerFactory->create($name);
        // Cheap hack to ensure logger name appears in exception message, should one occur
        $loggerOptions["name"] = $name;
        $logger->setHandlers($this->resolveHandlers($loggerOptions, $this->handlers));
        $this->addProcessors($logger, $this->resolveProcessors($loggerOptions, $this->processors));

        return $logger;
    }
}
