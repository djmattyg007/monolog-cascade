<?php
/**
 * This file is part of the Monolog Cascade package.
 *
 * (c) Raphael Antonmattei <rantonmattei@theorchard.com>
 * (c) The Orchard
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
use Monolog\Registry;

/**
 * Class ClassLoaderTest
 *
 * @author Raphael Antonmattei <rantonmattei@theorchard.com>
 */
class ClassLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Set up function
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Tear down function
     */
    public function tearDown()
    {
        ClassLoader::$extraOptionHandlers = array();
        parent::tearDown();
    }

    /**
     * Provides options with and without a class param
     * @return array of args
     */
    public function dataFortestSetClass()
    {
        return array(
            array(
                array(
                    'class' => SampleClass::class,
                    'some_param' => 'abc'
                ),
                SampleClass::class
            ),
            array(
                array(
                    'some_param' => 'abc'
                ),
                '\stdClass'
            )
        );
    }

    /**
     * Testing the setClass method
     *
     * @param  array $options array of options
     * @dataProvider dataFortestSetClass
     */
    public function testSetClass($options, $expectedClass)
    {
        $loader = new ClassLoader($options);

        $this->assertEquals($expectedClass, $loader->class);
    }

    public function testGetExtraOptionsHandler()
    {
        ClassLoader::$extraOptionHandlers = array(
            '*' => array(
                'hello' => function ($instance, $value) {
                    $instance->setHello(strtoupper($value));
                }
            ),
            SampleClass::class => array(
                'there' => function ($instance, $value) {
                    $instance->setThere(strtoupper($value).'!!!');
                }
            )
        );

        $loader = new ClassLoader(array());
        $existingHandler = $loader->getExtraOptionsHandler('hello');
        $this->assertNotNull($existingHandler);
        $this->assertTrue(is_callable($existingHandler));

        $this->assertNull($loader->getExtraOptionsHandler('nohandler'));
    }

    public function testLoad()
    {
        $options = array(
            'class' => SampleClass::class,
            'mandatory' => 'someValue',
            'optionalX' => 'testing some stuff',
            'optionalY' => 'testing other stuff',
            'hello' => 'hello',
            'there' => 'there',
        );

        ClassLoader::$extraOptionHandlers = array(
            '*' => array(
                'hello' => function ($instance, $value) {
                    $instance->setHello(strtoupper($value));
                }
            ),
            SampleClass::class => array(
                'there' => function ($instance, $value) {
                    $instance->setThere(strtoupper($value).'!!!');
                }
            )
        );

        $loader = new ClassLoader($options);
        $instance = $loader->load();

        $expectedInstance = new SampleClass('someValue');
        $expectedInstance->optionalX('testing some stuff');
        $expectedInstance->optionalY = 'testing other stuff';
        $expectedInstance->setHello('HELLO');
        $expectedInstance->setThere('THERE!!!');

        $this->assertEquals($expectedInstance, $instance);
    }

    /**
     * Test a nested class to load
     *
     * @author Dom Morgan <dom@d3r.com>
     */
    public function testLoadDependency()
    {
        $options = array(
            'class' => DependentClass::class,
            'dependency' => array(
                'class' => SampleClass::class,
                'mandatory' => 'someValue',
            )
        );

        $loader = new ClassLoader($options);
        $instance = $loader->load();

        $expectedInstance = new DependentClass(
            new SampleClass('someValue')
        );

        $this->assertEquals($expectedInstance, $instance);
    }
}
