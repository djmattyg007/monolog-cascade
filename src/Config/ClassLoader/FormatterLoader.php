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

use MattyG\MonologCascade\Config\ClassLoader;
use Monolog\Formatter\LineFormatter;

/**
 * Formatter Loader. Loads the Formatter options, validate them and instantiates
 * a Formatter object (implementing Monolog\Formatter\FormatterInterface) with all
 * the corresponding options
 *
 * @author Raphael Antonmattei <rantonmattei@theorchard.com>
 * @author Matthew Gamble
 */
class FormatterLoader extends ClassLoader
{
    // Default formatter class to use if none is provided in the option array.
    public const DEFAULT_CLASS = LineFormatter::class;

    /**
     * @see Monolog\Formatter classes for formatter options
     *
     * @param array $formatterOptions Formatter options.
     */
    public function __construct(array $formatterOptions)
    {
        parent::__construct($formatterOptions);

        self::initExtraOptionsHandlers();
    }

    /**
     * Loads the closures as option handlers. Add handlers to this function if
     * you want to support additional custom options.
     *
     * The syntax is the following:
     *     [
     *         \Full\Absolute\Namespace\ClassName::class => [
     *             'myOption' => Closure,
     *         ], ...
     *     ]
     *
     * You can use the '*' wildcard if you want to set up an option for all
     * Formatter classes.
     *
     * @todo add handlers to handle extra options for all known Monolog formatters
     */
    public static function initExtraOptionsHandlers(): void
    {
        self::$extraOptionHandlers = [
            LineFormatter::class => [
                "includeStacktraces" => function ($instance, $include) {
                    $instance->includeStacktraces($include);
                }
            ],
        ];
    }
}
