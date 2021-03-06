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
use MattyG\MonologCascade\Config\ClassLoader;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\LogglyHandler;
use Monolog\Handler\StreamHandler;

/**
 * Handler Loader. Loads the Handler options, validate them and instantiates
 * a Handler object (implementing Monolog\Handler\HandlerInterface) with all
 * the corresponding options
 * @see ClassLoader
 *
 * @author Raphael Antonmattei <rantonmattei@theorchard.com>
 * @author Matthew Gamble
 */
class HandlerLoader extends ClassLoader
{
    // Default handler class to use if none is provided in the option array.
    public const DEFAULT_CLASS = StreamHandler::class;

    /**
     * @see ClassLoader::__construct
     * @see Monolog\Handler classes for handler options
     *
     * @param array $handlerOptions Handler options.
     * @param FormatterInterface[] $formatters Array of formatter to pick from.
     * @param callable[] $processors Array of processors to pick from.
     */
    public function __construct(
        array &$handlerOptions,
        array $formatters = [],
        array $processors = []
    ) {
        $this->populateFormatters($handlerOptions, $formatters);
        $this->populateProcessors($handlerOptions, $processors);
        parent::__construct($handlerOptions);

        self::initExtraOptionsHandlers();
    }

    /**
     * Replace the formatter name in the option array with the corresponding object from the
     * formatter array passed in if it exists.
     *
     * If no formatter is specified in the options, Monolog will use its default formatter for the handler.
     *
     * @param array &$handlerOptions Handler options.
     * @param Monolog\Formatter\FormatterInterface[] $formatters Array of formatter to pick from.
     * @throws InvalidArgumentException
     */
    private function populateFormatters(array &$handlerOptions, array $formatters): void
    {
        if (isset($handlerOptions["formatter"])) {
            if (isset($formatters[$handlerOptions["formatter"]])) {
                $handlerOptions["formatter"] = $formatters[$handlerOptions["formatter"]];
            } else {
                throw new InvalidArgumentException(
                    sprintf(
                        'Formatter %s not found in the configured formatters',
                        $handlerOptions["formatter"]
                    )
                );
            }
        }
    }

    /**
     * Replace the processors in the option array with the corresponding callable from the
     * array of loaded and callable processors, if it exists.
     *
     *
     * @param array &$handlerOptions Handler options.
     * @param callable[] $processors Array of processors to pick from.
     * @throws InvalidArgumentException
     */
    private function populateProcessors(array &$handlerOptions, array $processors): void
    {
        $processorArray = [];

        if (isset($handlerOptions["processors"])) {
            foreach ($handlerOptions["processors"] as $processorId) {
                if (isset($processors[$processorId])) {
                    $processorArray[] = $processors[$processorId];
                } else {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Cannot add processor "%s" to the handler. Processor not found.',
                            $processorId
                        )
                    );
                }
            }

            $handlerOptions["processors"] = $processorArray;
        }
    }

    /**
     * Loads the closures as option handlers. Add handlers to this function if
     * you want to support additional custom options.
     *
     * The syntax is the following:
     *     [
     *         \Full\Absolute\Namespace\ClassName::class => [
     *             'myOption' => Closure
     *         ], ...
     *     ]
     *
     * You can use the '*' wildcard if you want to set up an option for all
     * Handler classes
     */
    public static function initExtraOptionsHandlers(): void
    {
        self::$extraOptionHandlers = [
            "*" => [
                "formatter" => function ($instance, FormatterInterface $formatter) {
                    $instance->setFormatter($formatter);
                },
                "processors" => function ($instance, array $processors) {
                    // We need to reverse the array because Monolog "pushes" processors to top of the stack
                    foreach (array_reverse($processors) as $processor) {
                        $instance->pushProcessor($processor);
                    }
                }
            ],
            LogglyHandler::class => [
                "tags" => function ($instance, $tags) {
                    $instance->setTag($tags);
                }
            ]
        ];
    }
}
