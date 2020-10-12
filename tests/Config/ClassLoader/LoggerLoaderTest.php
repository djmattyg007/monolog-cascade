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

namespace MattyG\MonologCascade\Tests\Config\ClassLoader;

use InvalidArgumentException;
use MattyG\MonologCascade\Config\ClassLoader\LoggerLoader;
use MattyG\MonologCascade\Monolog\LoggerFactory;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

/**
 * @author Raphael Antonmattei <rantonmattei@theorchard.com>
 * @author Matthew Gamble
 */
class LoggerLoaderTest extends TestCase
{
    /**
     * @var LoggerLoader
     */
    protected $loggerFactory;

    public function setUp(): void
    {
        $this->loggerFactory = new LoggerFactory();
        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->loggerFactory = null;
        parent::tearDown();
    }

    public function testConstructor()
    {
        $loader = new LoggerLoader($this->loggerFactory);
        $this->assertInstanceOf(LoggerLoader::class, $loader);
    }

    public function testResolveHandlers()
    {
        $options = array(
            "handlers" => array("test_handler_1", "test_handler_2"),
            "name" => "testLogger",
        );
        $handlers = array(
            "test_handler_1" => new TestHandler(),
            "test_handler_2" => new TestHandler(),
        );
        $loader = new LoggerLoader($this->loggerFactory, $handlers);

        $this->assertEquals(
            array_values($handlers),
            $loader->resolveHandlers($options, $handlers)
        );
    }

    public function testResolveHandlersWithMismatch()
    {
        $this->expectException(InvalidArgumentException::class);

        $options = array(
            "handlers" => array("nonexisting_handler", "test_handler_2"),
            "name" => "testLogger",
        );
        $handlers = array(
            "test_handler_1" => new TestHandler(),
            "test_handler_2" => new TestHandler(),
        );
        $loader = new LoggerLoader($this->loggerFactory, $handlers);

        // This should throw an InvalidArgumentException
        $loader->resolveHandlers($options, $handlers);
    }

    public function testResolveProcessors()
    {
        $dummyClosure = function () {
            // Empty function
        };
        $options = array(
            "processors" => array("test_processor_1", "test_processor_2"),
            "name" => "testLogger",
        );
        $processors = array(
            "test_processor_1" => $dummyClosure,
            "test_processor_2" => $dummyClosure,
        );

        $loader = new LoggerLoader($this->loggerFactory, array(), $processors);

        $this->assertEquals(
            array_values($processors),
            $loader->resolveProcessors($options, $processors)
        );
    }

    public function testResolveProcessorsWithMismatch()
    {
        $this->expectException(InvalidArgumentException::class);

        $dummyClosure = function () {
            // Empty function
        };
        $options = array(
            "processors" => ["nonexisting_processor", "test_processor_2"],
            "name" => "testLogger",
        );
        $processors = [
            "test_processor_1" => $dummyClosure,
            "test_processor_2" => $dummyClosure,
        ];
        $loader = new LoggerLoader($this->loggerFactory, array(), $processors);

        // This should throw an InvalidArgumentException
        $loader->resolveProcessors($options, $processors);
    }

    public function testLoad()
    {
        $options = [
            "handlers" => array("test_handler_1", "test_handler_2"),
            "processors" => array("test_processor_1", "test_processor_2"),
        ];
        $handlers = [
            "test_handler_1" => new TestHandler(),
            "test_handler_2" => new TestHandler(),
        ];
        $dummyClosure = function () {
            // Empty function
        };
        $processors = [
            "test_processor_1" => $dummyClosure,
            "test_processor_2" => $dummyClosure,
        ];

        $loader = new LoggerLoader($this->loggerFactory, $handlers, $processors);
        $logger = $loader->load("testLogger", $options);

        $this->assertTrue($logger instanceof Logger);
        $this->assertEquals(array_values($handlers), $logger->getHandlers());
        $this->assertEquals(array_values($processors), $logger->getProcessors());
    }
}
