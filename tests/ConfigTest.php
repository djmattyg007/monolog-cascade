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
namespace MattyG\MonologCascade\Tests;

use MattyG\MonologCascade\Config;
use MattyG\MonologCascade\Monolog\LoggerFactory;
use MattyG\MonologCascade\Config\ClassLoader\FormatterLoader;
use MattyG\MonologCascade\Config\ClassLoader\HandlerLoader;
use MattyG\MonologCascade\Tests\Fixtures;
use Monolog\Handler\HandlerInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Registry;

/**
 * Class ConfigTest
 *
 * @author Raphael Antonmattei <rantonmattei@theorchard.com>
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    use Reflector;

    /**
     * @var LoggerLoader
     */
    protected $loggerFactory;

    public function setUp()
    {
        $this->loggerFactory = new LoggerFactory();
        parent::setUp();
    }

    public function tearDown()
    {
        $this->loggerFactory = null;
        parent::tearDown();
    }

    /**
     * @param string[] $keys
     * @param array|ArrayAccess $array
     */
    protected static function assertArrayHasKeys($keys, $array)
    {
        foreach ($keys as $key) {
            self::assertArrayHasKey($key, $array);
        }
    }

    /**
     * @param string $id
     * @param array $objects
     * @param array $options
     * @param string $default
     */
    protected static function assertObjectIsConfiguredClassOrDefault($id, array $objects, array $options, $default)
    {
        self::assertInstanceOf(isset($options[$id]["class"]) ? $options[$id]["class"] : $default, $objects[$id]);
    }

    public function testConfigureFormatters()
    {
        $options = Fixtures::getArrayConfig();
        $configurer = new Config($options, $this->loggerFactory);
        $testMethod = $this->getNonPublicMethod(get_class($configurer), "configureFormatters");
        $fOptions = $options["formatters"];
        $formatters = $testMethod->invokeArgs($configurer, array($fOptions));

        $this->assertArrayHasKeys(array("spaced", "dashed"), $formatters);

        $this->assertObjectIsConfiguredClassOrDefault("spaced", $formatters, $fOptions, FormatterLoader::DEFAULT_CLASS);
        $this->assertObjectIsConfiguredClassOrDefault("dashed", $formatters, $fOptions, FormatterLoader::DEFAULT_CLASS);
        $this->assertContainsOnlyInstancesOf(FormatterInterface::class, $formatters);
    }

    public function testConfigureProcessors()
    {
        $options = Fixtures::getArrayConfig();
        $configurer = new Config($options, $this->loggerFactory);
        $testMethod = $this->getNonPublicMethod(get_class($configurer), "configureProcessors");
        $processors = $testMethod->invokeArgs($configurer, array($options["processors"]));

        $this->assertArrayHasKey("tag_processor", $processors);

        $this->assertContainsOnly("callable", $processors, true);
    }

    /**
     * Test configure throwing an exception due to missing formatter with name 'spaced'.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testConfigureHandlersWithNoFormatters()
    {
        $options = Fixtures::getArrayConfig();
        $configurer = new Config($options, $this->loggerFactory);
        $testMethod = $this->getNonPublicMethod(get_class($configurer), "configureHandlers");
        $handlers = $testMethod->invokeArgs($configurer, array($options["handlers"]));
    }

    public function testConfigureHandlersWithFormatters()
    {
        $options = Fixtures::getArrayConfig();
        $configurer = new Config($options, $this->loggerFactory);
        $testMethod = $this->getNonPublicMethod(get_class($configurer), "configureHandlers");
        // Get mock implementations of all formatters so they can be resolved for handlers that require them.
        $formatters = $this->buildFormattersForHandlers($options["formatters"]);
        $hOptions = $options["handlers"];
        $handlers = $testMethod->invokeArgs($configurer, array($hOptions, $formatters));

        $handlerIds = array("console", "info_file_handler", "error_file_handler");
        $this->assertArrayHasKeys($handlerIds, $handlers);

        foreach ($handlerIds as $handlerId) {
            $this->assertObjectIsConfiguredClassOrDefault($handlerId, $handlers, $hOptions, HandlerLoader::DEFAULT_CLASS);
        }
        $this->assertContainsOnlyInstancesOf(HandlerInterface::class, $handlers);
    }

    /**
     * @param array $formatterConfiguration
     * @return \Monolog\Formatter\FormatterInterface[]
     */
    protected function buildFormattersForHandlers(array $formatterConfiguration)
    {
        // Handlers shouldn't care what formatter is used as long as it implements the FormatterInterface.
        $mock = $this->getMockBuilder(FormatterInterface::class)->getMock();
        $mock->method("format")->will($this->returnCallback("json_encode"));
        $mock->method("formatBatch")->will($this->returnCallback("json_encode"));

        $formatters = array();
        foreach ($formatterConfiguration as $formatterId => $formatterOptions) {
            $formatters[$formatterId] = clone $mock;
        }
        return $formatters;
    }

    /**
     * Test configure throwing an exception due to missing 'loggers' key.
     *
     * @expectedException \RuntimeException
     */
    public function testConfigureWithNoLoggers()
    {
        $options = array();

        // Mocking the config object
        $configurer = $this->getMockBuilder(Config::class)
            ->setConstructorArgs(array($options, $this->loggerFactory))
            ->setMethods(null)
            ->getMock();

        // This should trigger an exception because there is no 'loggers' key in the options passed in.
        $configurer->configure();
    }

    public function testLoggersConfigured()
    {
        $options = Fixtures::getArrayConfig();
        $configurer = new Config($options, $this->loggerFactory);

        $loggers = $configurer->configure();
        $this->assertArrayHasKey("my_logger", $loggers);
        $this->assertInstanceOf(Logger::class, $loggers["my_logger"]);
    }
}
