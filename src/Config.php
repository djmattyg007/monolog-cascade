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

use MattyG\MonologCascade\Monolog\LoggerFactory;
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
     * Array of logger configuration
     *
     * @var array
     */
    protected $configuration = array();

    /**
     * @var LoggerFactory
     */
    protected $loggerFactory;

    /**
     * @param array $options
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(array $options, LoggerFactory $loggerFactory)
    {
        $this->configuration = $options;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * Configure and register Logger(s) according to the options passed in.
     *
     * @return \Monolog\Logger[]
     */
    public function configure()
    {
        if (isset($this->configuration['formatters'])) {
            $formatters = $this->configureFormatters($this->configuration['formatters']);
        } else {
            $formatters = array();
        }

        if (isset($this->configuration['processors'])) {
            $processors = $this->configureProcessors($this->configuration['processors']);
        } else {
            $processors = array();
        }

        if (isset($this->configuration['handlers'])) {
            $handlers = $this->configureHandlers($this->configuration['handlers']);
        } else {
            $handlers = array();
        }

        if (isset($this->configuration['loggers'])) {
            return $this->configureLoggers($this->configuration['loggers'], $handlers, $processors);
        } else {
            throw new \RuntimeException(
                'Cannot configure loggers. No logger configuration options provided.'
            );
        }
    }

    /**
     * Configure all formatters
     *
     * @param array $formatterConfiguration An array of formatter options
     */
    protected function configureFormatters(array $formatterConfiguration = array())
    {
        $formatters = array();
        foreach ($formatterConfiguration as $formatterId => $formatterOptions) {
            $formatterLoader = new FormatterLoader($formatterOptions);
            $formatters[$formatterId] = $formatterLoader->load();
        }
        return $formatters;
    }

    /**
     * Configure all processors
     *
     * @param array $processorConfiguration An array of processor options
     * @return callable[]
     */
    protected function configureProcessors(array $processorConfiguration)
    {
        $processors = array();
        foreach ($processorConfiguration as $processorName => $processorOptions) {
            $processorLoader = new ProcessorLoader($processorOptions, $processors);
            $processors[$processorName] = $processorLoader->load();
        }
        return $processors;
    }

    /**
     * Configure all handlers
     *
     * @param array $handlers An array of handler options
     * @param array $formatters An array of all configured formatters
     * @param array $processors An array of all configured processors
     * @return HandlerInterface[]
     */
    protected function configureHandlers(array $handlerConfiguration, $formatters = array(), $processors = array())
    {
        $handlers = array();
        foreach ($handlerConfiguration as $handlerId => $handlerOptions) {
            $handlerLoader = new HandlerLoader($handlerOptions, $formatters, $processors);
            $handlers[$handlerId] = $handlerLoader->load();
        }
        return $handlers;
    }

    /**
     * Configure all loggers
     *
     * @param array $loggers An array of logger options
     * @param HandlerInterface[] $handlers
     * @param callable[] $processors
     * @return \Monolog\Logger[]
     */
    protected function configureLoggers(array $loggerConfiguration, array $handlers, array $processors)
    {
        $loggerLoader = new LoggerLoader($this->loggerFactory, $handlers, $processors);
        $loggers = array();
        foreach ($loggerConfiguration as $loggerName => $loggerOptions) {
            $loggers[$loggerName] = $loggerLoader->load($loggerName, $loggerOptions);
        }
        return $loggers;
    }
}
