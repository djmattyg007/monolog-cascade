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

class Fixtures
{
    /**
     * Return the fixture directory.
     *
     * @return string Fixture directory
     */
    public static function fixtureDir()
    {
        return realpath(__DIR__ . "/Fixtures");
    }

    /**
     * Return a path to a non existing file.
     *
     * @return string Wrong file path
     */
    public static function getInvalidFile()
    {
        return "some/non/existing/file.txt";
    }

    /**
     * Return a sample string.
     *
     * @return string Sample string
     */
    public static function getSampleString()
    {
        return " some string with new \n\n lines and white spaces \n\n";
    }

    /**
     * Return a config array.
     *
     * @return array Config array
     */
    public static function getArrayConfig()
    {
        require self::fixtureDir() . "/fixture_config.php";

        return $fixtureArray;
    }

    /**
     * Return a sample array.
     *
     * @return array Sample array
     */
    public static function getSamplePhpArray()
    {
        return array(
            'greeting' => 'hello',
            'to' => 'you'
        );
    }
}
