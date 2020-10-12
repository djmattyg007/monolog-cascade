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

namespace MattyG\MonologCascade\Tests\Config;

use MattyG\MonologCascade\Config\ClassLoader;
use MattyG\MonologCascade\Tests\Fixtures\SampleClass;
use MattyG\MonologCascade\Tests\Fixtures\DependentClass;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

/**
 * @author Raphael Antonmattei <rantonmattei@theorchard.com>
 */
class ClassLoaderTest extends TestCase
{
    public function tearDown(): void
    {
        ClassLoader::$extraOptionHandlers = [];
        parent::tearDown();
    }

    /**
     * Provides options with and without a class param.
     *
     * @return array
     */
    public function dataForTestSetClass(): array
    {
        return [
            [
                [
                    "class" => SampleClass::class,
                    "some_param" => "abc",
                ],
                SampleClass::class,
            ],
            [
                [
                    "some_param" => "abc",
                ],
                // TODO: Test changing this to stdClass::class
                "\\stdClass",
            ],
        ];
    }

    /**
     * Testing the setClass method.
     *
     * @dataProvider dataForTestSetClass
     * @param array $options Array of options.
     */
    public function testSetClass($options, $expectedClass): void
    {
        $loader = new ClassLoader($options);

        $this->assertEquals($expectedClass, $loader->class);
    }

    public function testGetExtraOptionsHandler()
    {
        ClassLoader::$extraOptionHandlers = [
            '*' => [
                "hello" => function ($instance, $value) {
                    $instance->setHello(strtoupper($value));
                }
            ],
            SampleClass::class => [
                "there" => function ($instance, $value) {
                    $instance->setThere(strtoupper($value) . "!!!");
                }
            ],
        ];

        $loader = new ClassLoader([]);
        $existingHandler = $loader->getExtraOptionsHandler("hello");
        $this->assertNotNull($existingHandler);
        $this->assertTrue(is_callable($existingHandler));

        $this->assertNull($loader->getExtraOptionsHandler("nohandler"));
    }

    public function testLoad()
    {
        $options = [
            "class" => SampleClass::class,
            "mandatory" => "someValue",
            "optionalX" => "testing some stuff",
            "optionalY" => "testing other stuff",
            "hello" => "hello",
            "there" => "there",
        ];

        ClassLoader::$extraOptionHandlers = [
            "*" => [
                "hello" => function ($instance, $value) {
                    $instance->setHello(strtoupper($value));
                }
            ],
            SampleClass::class => [
                "there" => function ($instance, $value) {
                    $instance->setThere(strtoupper($value) . "!!!");
                }
            ]
        ];

        $loader = new ClassLoader($options);
        $instance = $loader->load();

        $expectedInstance = new SampleClass("someValue");
        $expectedInstance->optionalX("testing some stuff");
        $expectedInstance->optionalY = "testing other stuff";
        $expectedInstance->setHello("HELLO");
        $expectedInstance->setThere("THERE!!!");

        $this->assertEquals($expectedInstance, $instance);
    }

    /**
     * Test a nested class to load.
     *
     * @author Dom Morgan <dom@d3r.com>
     */
    public function testLoadDependency()
    {
        $options = [
            "class" => DependentClass::class,
            "dependency" => [
                "class" => SampleClass::class,
                "mandatory" => "someValue",
            ],
        ];

        $loader = new ClassLoader($options);
        $instance = $loader->load();

        $expectedInstance = new DependentClass(
            new SampleClass("someValue")
        );

        $this->assertEquals($expectedInstance, $instance);
    }
}
