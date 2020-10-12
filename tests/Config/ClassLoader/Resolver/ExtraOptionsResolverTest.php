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

namespace MattyG\MonologCascade\Tests\Config\ClassLoader\Resolver;

use MattyG\MonologCascade\Config\ClassLoader;
use MattyG\MonologCascade\Config\ClassLoader\Resolver\ExtraOptionsResolver;
use MattyG\MonologCascade\Tests\Fixtures\SampleClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/**
 * @author Raphael Antonmattei <rantonmattei@theorchard.com>
 */
class ExtraOptionsResolverTest extends TestCase
{
    /**
     * Reflection class for which you want to resolve extra options.
     *
     * @var ReflectionClass
     */
    protected $reflected = null;

    /**
     * ExtraOptions resolver.
     *
     * @var ExtraOptionsResolver
     */
    protected $resolver = null;

    public function setUp(): void
    {
        $this->class = SampleClass::class;
        $this->params = ["optionalA", "optionalB"];
        $this->resolver = new ExtraOptionsResolver(
            new ReflectionClass($this->class),
            $this->params
        );
        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->resolver = null;
        $this->class = null;
        parent::tearDown();
    }

    /**
     * Test the hsah key generation.
     */
    public function testGenerateParamsHashKey()
    {
        $a = ["optionA", "optionB", "optionC"];
        $b = ["optionA", "optionB", "optionC"];

        $this->assertSame(
            ExtraOptionsResolver::generateParamsHashKey($a),
            ExtraOptionsResolver::generateParamsHashKey($b)
        );
    }

    /**
     * Test the resolver contructor.
     */
    public function testConstructor()
    {
        $this->assertSame($this->class, $this->resolver->getReflected()->getName());
        $this->assertEquals($this->params, $this->resolver->getParams());
    }

    /**
     * Test resolving with valid options
     */
    public function testResolve()
    {
        $this->assertEquals(
            array_combine($this->params, ["hello", "there"]),
            $this->resolver->resolve(["optionalB" => "there", "optionalA" => "hello"])
        );

        // Resolve an empty array (edge case)
        $this->assertEquals([], $this->resolver->resolve([]));
    }

    /**
     * Data provider for testResolveWithInvalidOptions
     *
     * The order of the input options does not matter and is somewhat random.
     *
     * @return array List of arrays with expected resolved values and options used as input.
     */
    public function optionsProvider()
    {
        return [
            [
                ["optionalA", "optionalB", "mandatory"],
                $this->getMockBuilder(ClassLoader::class)
                    ->disableOriginalConstructor()
                    ->getMock()->method("canHandle")
                    ->willReturn(true),
            ]
        ];
    }

    /**
     * Test resolving with valid options
     */
    public function testResolveWithCustomOptionHandler()
    {
        $this->params = ["optionalA", "optionalB", "mandatory"];
        $this->resolver = new ExtraOptionsResolver(
            new ReflectionClass($this->class),
            $this->params
        );

        // Create a stub for the SomeClass class.
        $stub = $this->getMockBuilder(ClassLoader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stub->method('canHandle')
            ->willReturn(true);

        // Resolve an empty array (edge case)
        $this->assertEquals(["mandatory" => "abc"], $this->resolver->resolve(["mandatory" => "abc"], $stub));
    }

    /**
     * Data provider for testResolveWithInvalidOptions
     *
     * The order of the input options does not matter and is somewhat random.
     *
     * @return array List of arrays with expected resolved values and options used as input.
     */
    public function invalidOptionsProvider()
    {
        return array(
            array(
                array( // Some invalid
                    'optionalB' => 'there',
                    'optionalA' => 'hello',
                    'additionalInvalid' => 'some unknow param'
                ),
                array( // All invalid
                    'someInvalidOptionA' => 'abc',
                    'someInvalidOptionB' => 'def'
                )
            )
        );
    }

    /**
     * Test resolving with invalid options.
     *
     * @dataProvider invalidOptionsProvider
     */
    public function testResolveWithInvalidOptions($invalidOptions)
    {
        $this->expectException(UndefinedOptionsException::class);

        $this->resolver->resolve($invalidOptions);
    }
}
