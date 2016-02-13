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

use MattyG\MonologCascade\Config\ClassLoader\FormatterLoader;
use MattyG\MonologCascade\Config\ClassLoader\HandlerLoader;
use MattyG\MonologCascade\Config\ClassLoader\LoggerLoader;
use MattyG\MonologCascade\Config\ClassLoader\ProcessorLoader;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Registry;

/**
 * Config class that takes an array of configuration and configures Loggers with
 * all the required options (Formatters, Handlers, etc.)
 *
 * @author Raphael Antonmattei <rantonmattei@theorchard.com>
 * @author Matthew Gamble
 */
class Config
{
    /**
     * Array of logger configuration options: (logger attributes, formatters, handlers, etc.)
     *
     * @var array
     */
    protected $options = array();

    /**
     * Array of Formatter objects
     *
     * @var FormatterInterface[]
     */
    protected $formatters = array();

    /**
     * Array of Handler objects
     *
     * @var HandlerInterface[]
     */
    protected $handlers = array();

    /**
     * Array of Processor objects
     *
     * @var callable[]
     */
    protected $processors = array();

    /**
     * Array of logger objects
     *
     * @var \Monolog\Logger[]
     */
    protected $loggers = array();

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * Configure and register Logger(s) according to the options passed in.
     */
    public function configure()
    {
        if (!isset($this->options['disable_existing_loggers'])) {
            // We disable any existing loggers by default
            $this->options['disable_existing_loggers'] = true;
        }

        if ($this->options['disable_existing_loggers']) {
            Registry::clear();
        }

        if (isset($this->options['formatters'])) {
            $this->configureFormatters($this->options['formatters']);
        }

        if (isset($this->options['processors'])) {
            $this->configureProcessors($this->options['processors']);
        }

        if (isset($this->options['handlers'])) {
            $this->configureHandlers($this->options['handlers']);
        }

        if (isset($this->options['loggers'])) {
            $this->configureLoggers($this->options['loggers']);
        } else {
            throw new \RuntimeException(
                'Cannot configure loggers. No logger configuration options provided.'
            );
        }
    }

    /**
     * Configure all formatters
     *
     * @param array $formatters An array of formatter options
     */
    protected function configureFormatters(array $formatters = array())
    {
        foreach ($formatters as $formatterId => $formatterOptions) {
            $formatterLoader = new FormatterLoader($formatterOptions);
            $this->formatters[$formatterId] = $formatterLoader->load();
        }
    }

    /**
     * Configure all handlers
     *
     * @param array $handlers An array of handler options
     */
    protected function configureHandlers(array $handlers)
    {
        foreach ($handlers as $handlerId => $handlerOptions) {
            $handlerLoader = new HandlerLoader($handlerOptions, $this->formatters, $this->processors);
            $this->handlers[$handlerId] = $handlerLoader->load();
        }
    }

    /**
     * Configure all processors
     *
     * @param array $processors An array of processor options
     */
    protected function configureProcessors(array $processors)
    {
        foreach ($processors as $processorName => $processorOptions) {
            $processorLoader = new ProcessorLoader($processorOptions, $this->processors);
            $this->processors[$processorName] = $processorLoader->load();
        }
    }

    /**
     * Configure all loggers
     *
     * @param array $loggers An array of logger options
     */
    protected function configureLoggers(array $loggers)
    {
        foreach ($loggers as $loggerName => $loggerOptions) {
            $loggerLoader = new LoggerLoader($loggerName, $loggerOptions, $this->handlers, $this->processors);
            $this->loggers[$loggerName] = $loggerLoader->load();
        }
    }
}
