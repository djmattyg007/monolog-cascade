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

use MattyG\MonologCascade\Config\ClassLoader\Resolver\ConstructorResolver;
use MattyG\MonologCascade\Tests\Fixtures\SampleClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/**
 * @author Raphael Antonmattei <rantonmattei@theorchard.com>
 */
class ConstructorResolverTest extends TestCase
{
    /**
     * Reflection class for which you want to resolve extra options
     *
     * @var ReflectionClass
     */
    protected $reflected = null;

    /**
     * Constructor Resolver
     *
     * @var ConstructorResolver
     */
    protected $resolver = null;

    public function setUp(): void
    {
        $this->class = SampleClass::class;
        $this->resolver = new ConstructorResolver(new ReflectionClass($this->class));
        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->resolver = null;
        $this->class = null;
        parent::tearDown();
    }

    /**
     * Return the contructor args of the reflected class.
     *
     * @return ReflectionParameter[] Array of constructor params.
     */
    protected function getConstructorArgs()
    {
        return $this->resolver->getReflected()->getConstructor()->getParameters();
    }

    /**
     * Test the resolver contructor.
     */
    public function testConstructor()
    {
        $this->assertSame($this->class, $this->resolver->getReflected()->getName());
    }

    /**
     * Test that constructor args were pulled properly.
     *
     * Notie that we need to deduplicate the CamelCase conversion here for old fashioned classes.
     * TODO: We probably don't need to do this anymore in the age of PHP7.
     */
    public function testInitConstructorArgs()
    {
        $expectedConstructorArgs = [];

        foreach ($this->getConstructorArgs() as $param) {
            $expectedConstructorArgs[$param->getName()] = $param;
        }
        $this->assertEquals($expectedConstructorArgs, $this->resolver->getConstructorArgs());
    }

    /**
     * Test the hashToArgsArray function.
     */
    public function testHashToArgsArray()
    {
        $this->assertEquals(
            ['someValue', 'hello', 'there', 'slither'],
            $this->resolver->hashToArgsArray(
                [
                    // Not properly ordered on purpose
                    'optionalB'         => 'there',
                    'optionalA'         => 'hello',
                    'optional_snake'    => 'slither',
                    'mandatory'         => 'someValue',
                ]
            )
        );
    }

    /**
     * Data provider for testResolve
     *
     * The order of the input options does not matter and is somewhat random. The resolution
     * should reconcile those options and match them up with the contructor param position.
     *
     * @return array List of arrays with expected resolved values and options used as input.
     */
    public function optionsProvider()
    {
        return [
            [
                ['someValue', 'hello', 'there', 'slither'], // Expected resolved options
                [
                    // Options (order should not matter, part of resolution)
                    'optionalB'      => 'there',
                    'optionalA'      => 'hello',
                    'mandatory'      => 'someValue',
                    'optional_snake' => 'slither',
                ]
            ],
            [
                ['someValue', 'hello', 'BBB', 'snake'],
                [
                    'mandatory' => 'someValue',
                    'optionalA' => 'hello',
                ]
            ],
            [
                ['someValue', 'AAA', 'BBB', 'snake'],
                ['mandatory' => 'someValue'],
            ],
        ];
    }

    /**
     * Test resolving with valid options
     *
     * @dataProvider optionsProvider
     */
    public function testResolve($expectedResolvedOptions, $options)
    {
        $this->assertEquals($expectedResolvedOptions, $this->resolver->resolve($options));
    }

    /**
     * Data provider for testResolveWithInvalidOptions
     *
     * The order of the input options does not matter and is somewhat random. The resolution
     * should reconcile those options and match them up with the contructor param position.
     *
     * @return array List of arrays with expected resolved values and options used as input.
     */
    public function missingOptionsProvider()
    {
        return [
            [
                // No values
                [],
                [
                    // Missing a mandatory value
                    'optionalB' => 'BBB',
                ],
                [
                    // Still missing a mandatory value
                    'optionalB' => 'there',
                    'optionalA' => 'hello',
                ],
            ],
        ];
    }

    /**
     * Test resolving with invalid options.
     *
     * @dataProvider missingOptionsProvider
     */
    public function testResolveWithMissingOptions($invalidOptions)
    {
        $this->expectException(MissingOptionsException::class);

        $this->resolver->resolve($invalidOptions);
    }

    /**
     * Data provider for testResolveWithInvalidOptions
     *
     * The order of the input options does not matter and is somewhat random. The resolution
     * should reconcile those options and match them up with the contructor param position.
     *
     * @return array of arrays with expected resolved values and options used as input.
     */
    public function invalidOptionsProvider()
    {
        return [
            [
                ['ABC'],
                [
                    // All invalid
                    'someInvalidOptionA' => 'abc',
                    'someInvalidOptionB' => 'def',
                ],
                [
                    // Some invalid
                    'optionalB' => 'there',
                    'optionalA' => 'hello',
                    'mandatory' => 'dsadsa',
                    'additionalInvalid' => 'some unknow param',
                ],
            ],
        ];
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
