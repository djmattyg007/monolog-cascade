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

use MattyG\MonologCascade\Cascade;
use MattyG\MonologCascade\Tests\Fixtures;
use Monolog\Logger;

/**
 * @author Raphael Antonmattei <rantonmattei@theorchard.com>
 * @author Matthew Gamble
 */
class CascadeTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Cascade::clear();
        parent::tearDown();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRegistryWithInvalidName()
    {
        $logger = Cascade::getLogger(null);
    }

    public function testConfigure()
    {
        $options = Fixtures::getArrayConfig();
        Cascade::configure($options);
        $this->assertInstanceOf(Logger::class, Cascade::getLogger("my_logger"));
    }
}
