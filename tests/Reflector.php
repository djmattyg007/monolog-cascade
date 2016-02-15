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

use ReflectionClass;

trait Reflector
{
    /**
     * @param string $class The class whose non-public method we need access to
     * @param string $method The name of the non-public method in question
     * @return \ReflectionMethod
     */
    public function getNonPublicMethod($class, $method)
    {
        $reflector = new ReflectionClass($class);
        $method = $reflector->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }
}
