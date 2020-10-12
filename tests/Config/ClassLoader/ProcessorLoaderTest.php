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

use MattyG\MonologCascade\Config\ClassLoader\ProcessorLoader;
use Monolog\Processor\WebProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @author Kate Burdon <kburdon@tableau.com>
 */
class ProcessorLoaderTest extends TestCase
{
    public function testProcessorLoader()
    {
        $options = array(
            'class' => WebProcessor::class,
        );
        $processors = array(new WebProcessor());
        $loader = new ProcessorLoader($options, $processors);

        $this->assertEquals($loader->class, $options['class']);
    }
}
