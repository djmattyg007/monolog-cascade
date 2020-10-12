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

use Exception;
use InvalidArgumentException;
use MattyG\MonologCascade\Config\ClassLoader\HandlerLoader;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\TestHandler;
use PHPUnit\Framework\TestCase;

/**
 * @author Raphael Antonmattei <rantonmattei@theorchard.com>
 */
class HandlerLoaderTest extends TestCase
{
    public function testHandlerLoader()
    {
        $dummyClosure = function () {
            // Empty function
        };
        $original = $options = [
            "class" => TestHandler::class,
            "level" => "DEBUG",
            "formatter" => "test_formatter",
            "processors" => ["test_processor_1", "test_processor_2"],
        ];
        $formatters = ["test_formatter" => new LineFormatter()];
        $processors = [
            "test_processor_1" => $dummyClosure,
            "test_processor_2" => $dummyClosure,
        ];
        $loader = new HandlerLoader($options, $formatters, $processors);

        $this->assertNotEquals($original, $options);
        $this->assertEquals(new LineFormatter(), $options["formatter"]);
        $this->assertContains($dummyClosure, $options["processors"]);
        $this->assertContains($dummyClosure, $options['processors']);
    }

    public function testHandlerLoaderWithNoOptions()
    {
        $original = $options = array();
        $loader = new HandlerLoader($options);

        $this->assertEquals($original, $options);
    }

    public function testHandlerLoaderWithInvalidFormatter()
    {
        $this->expectException(InvalidArgumentException::class);

        $options = [
            "formatter" => "test_formatter",
        ];

        $formatters = ["test_formatterXYZ" => new LineFormatter()];
        $loader = new HandlerLoader($options, $formatters);
    }

    public function testHandlerLoaderWithInvalidProcessor()
    {
        $this->expectException(InvalidArgumentException::class);

        $dummyClosure = function () {
            // Empty function
        };
        $options = [
            "processors" => ["test_processor_1"],
        ];

        $formatters = [];
        $processors = ["test_processorXYZ" => $dummyClosure];
        $loader = new HandlerLoader($options, $formatters, $processors);
    }

    /**
     * Check if the handler exists for a given class and option.
     * Also checks that it a callable and return it.
     *
     * @param string $class Class name the handler applies to.
     * @param string $optionName
     * @return Closure
     */
    private function getHandler($class, $optionName)
    {
        if (isset(HandlerLoader::$extraOptionHandlers[$class][$optionName])) {
            // Get the closure
            $closure = HandlerLoader::$extraOptionHandlers[$class][$optionName];

            $this->assertTrue(is_callable($closure));

            return $closure;
        } else {
            throw new Exception(
                sprintf(
                    'Custom handler %s is not defined for class %s',
                    $optionName,
                    $class
                )
            );
        }
    }

    /**
     * Tests that calling the given Closure will trigger a method call with the given param
     * in the given class.
     *
     * @param string $class Class name.
     * @param string $methodName Method name.
     * @param mixed $methodArg Parameter passed to the closure.
     * @param Closure $closure Closure to call.
     */
    private function doTestMethodCalledInHandler($class, $methodName, $methodArg, \Closure $closure)
    {
        // Setup mock and expectations
        $mock = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->setMethods(array($methodName))
            ->getMock();

        $mock->expects($this->once())
            ->method($methodName)
            ->with($methodArg);

        // Calling the handler
        $closure($mock, $methodArg);
    }


    /**
     * Test that handlers exist.
     */
    public function testHandlersExist()
    {
        $options = [];
        new HandlerLoader($options);
        $this->assertNotEmpty(HandlerLoader::$extraOptionHandlers);
    }

    /**
     * Data provider for testHandlers
     * /!\ Important note:
     * Just add values to this array if you need to test a newly added handler.
     *
     * If one of your handlers calls more than one method you can add more than one entries.
     *
     * Each array should look like this:
     * [
     *   "Namespace\Classname",
     *   "optionName",
     *   "optionTestValue",
     *   "methodNameForHandlerToCall",
     * ]
     *
     * @return array Array of args for testHandlers
     */
    public function handlerParamsProvider(): array
    {
        return [
            [
                "*",
                "formatter",
                new LineFormatter(),
                'setFormatter',
            ],
            [
                \Monolog\Handler\LogglyHandler::class,
                'tags',
                array('some_tag'),
                'setTag',
            ],
        ];
    }

    /**
     * Test the extra option handlers.
     *
     * @dataProvider handlerParamsProvider
     */
    public function testHandlers($class, $optionName, $optionValue, $calledMethodName)
    {
        $options = array();
        new HandlerLoader($options);
        // Test if handler exists and return it
        $closure = $this->getHandler($class, $optionName);

        if ($class === "*") {
            $class = TestHandler::class;
        }

        $this->doTestMethodCalledInHandler($class, $calledMethodName, $optionValue, $closure);
    }

    /**
     * Test extra option processor handler
     */
    public function testHandlerForProcessor()
    {
        $options = array();

        $mockProcessor1 = "123";
        $mockProcessor2 = "456";
        $processorsArray = array($mockProcessor1, $mockProcessor2);

        // Setup mock and expectations
        $mockHandler = $this->getMockBuilder(TestHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(["pushProcessor"])
            ->getMock();

        $mockHandler->expects($this->exactly(sizeof($processorsArray)))
            ->method("pushProcessor")
            ->withConsecutive(array($mockProcessor2), array($mockProcessor1));

        new HandlerLoader($options);
        $closure = $this->getHandler('*', 'processors');
        $closure($mockHandler, $processorsArray);
    }
}
