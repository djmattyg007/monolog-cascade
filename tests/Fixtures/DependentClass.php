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

namespace MattyG\MonologCascade\Tests\Fixtures;

/**
 * @author Raphael Antonmattei <rantonmattei@theorchard.com>
 */
class DependentClass
{
    /**
     * An object dependency.
     *
     * @var MattyG\MonologCascade\Tests\Fixtures\SampleClass
     */
    private $dependency;

    /**
     * Constructor
     *
     * @param mixed $mandatory Some mandatory param.
     * @param string $optionalA Some optional param.
     */
    public function __construct(SampleClass $dependency)
    {
        $this->setDependency($dependency);
    }

    /**
     * Set the object dependency
     *
     * @param MattyG\MonologCascade\Tests\Fixtures\SampleClass $dependency Some value.
     */
    public function setDependency($dependency)
    {
        $this->dependency = $dependency;
    }
}
