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
namespace MattyG\MonologCascade\Tests;

use MattyG\MonologCascade\Config;
use MattyG\MonologCascade\Tests\Fixtures;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Monolog\Registry;

/**
 * Class ConfigTest
 *
 * @author Raphael Antonmattei <rantonmattei@theorchard.com>
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigure()
    {
        $options = Fixtures::getPhpArrayConfig();

        // Mocking the config object and set expectations for the configure methods
        $config = $this->getMockBuilder(Config::class)
            ->setConstructorArgs(array($options))
            ->setMethods(array(
                    'configureFormatters',
                    'configureProcessors',
                    'configureHandlers',
                    'configureLoggers'
                ))
            ->getMock();

        $config->expects($this->once())->method('configureFormatters');
        $config->expects($this->once())->method('configureProcessors');
        $config->expects($this->once())->method('configureHandlers');
        $config->expects($this->once())->method('configureLoggers');

        $config->configure();
    }

    /**
     * Test configure throwing an exception due to missing 'loggers' key
     * @expectedException \RuntimeException
     */
    public function testConfigureWithNoLoggers()
    {
        $options = array();

        // Mocking the config object
        $config = $this->getMockBuilder(Config::class)
            ->setConstructorArgs(array($options))
            ->setMethods(null)
            ->getMock();

        // This should trigger an exception because there is no 'loggers' key in
        // the options passed in
        $config->configure();
    }

    public function testLoggersConfigured()
    {
        $options = Fixtures::getPhpArrayConfig();
        $config = new Config($options);

        $config->configure();

        $this->assertTrue(Registry::hasLogger('my_logger'));
    }
}
