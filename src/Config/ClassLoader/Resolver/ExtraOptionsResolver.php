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

namespace MattyG\MonologCascade\Config\ClassLoader\Resolver;

use ReflectionClass;
use MattyG\MonologCascade\Config\ClassLoader;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Extra options resolver. Set up an option resolver for the passed in params and
 * apply validation rules if any
 *
 * @author Raphael Antonmattei <rantonmattei@theorchard.com>
 * @author Matthew Gamble
 */
class ExtraOptionsResolver
{
    /**
     * Reflection class for which you want to resolve extra options.
     *
     * @var ReflectionClass
     */
    protected $reflected = null;

    /**
     * Registry of resolvers.
     *
     * @var array
     */
    private static $resolvers = [];

    /**
     * Associative array of parameters to resolve against.
     *
     * @var array
     */
    protected $params = [];

    /**
     * @param ReflectionClass $reflected Reflection class for which you want to resolve extra options.
     * @param array $params An associative array of extra parameters we want to resolve against.
     */
    public function __construct(ReflectionClass $reflected, array $params = [])
    {
        $this->reflected = $reflected;
        $this->setParams($params);
    }

    /**
     * Set the parameters we want to resolve against.
     *
     * @param array $params Associative array of extra parameters we want to resolve against.
     */
    public function setParams(array $params = []): void
    {
        $this->params = $params;
    }

    /**
     * Get the parameters we want to resolve against.
     *
     * @return array $params Associative array of parameters.
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Returns the reflected object
     *
     * @return ReflectionClass
     */
    public function getReflected(): ReflectionClass
    {
        return $this->reflected;
    }

    /**
     * Generate a unique hash based on the keys of the extra params.
     *
     * @param array $params Array of parameters.
     * @return string Unique MD5 hash.
     */
    public static function generateParamsHashKey($params): string
    {
        return md5(serialize($params));
    }

    /**
     * Configure options for the provided OptionResolver to match extra params requirements.
     *
     * @param OptionsResolver $optionsResolver OptionResolver to configure.
     * @param ClassLoader|null $classLoader Optional class loader if you want to use custom
     *      handlers for some of the extra options.
     */
    protected function configureOptions(OptionsResolver $resolver, ClassLoader $classLoader = null): void
    {
        foreach ($this->params as $name) {
            if ($this->reflected->hasMethod($name)) {
                // There is a method to handle this option
                $resolver->setDefined($name);
                continue;
            }
            if (
                $this->reflected->hasProperty($name) &&
                $this->reflected->getProperty($name)->isPublic()
            ) {
                // There is a public member we can set to handle this option
                $resolver->setDefined($name);
                continue;
            }

            // Option that cannot be handled by a regular setter but
            // requires specific pre-processing and/or handling to be set
            // e.g. like LogglyHandler::addTag for instance
            if (!is_null($classLoader) && $classLoader->canHandle($name)) {
                $resolver->setDefined($name);
            }
        }
    }

    /**
     * Resolve options against extra params requirements.
     *
     * @param array $options Array of option values.
     * @param ClassLoader|null $classLoader Optional class loader if you want to use custom
     *      handlers to resolve the extra options.
     * @return array Array of resolved options.
     */
    public function resolve($options, ClassLoader $classLoader = null): array
    {
        $hashKey = self::generateParamsHashKey($this->params);

        // Was configureOptions() executed before for this class?
        if (!isset(self::$resolvers[$hashKey])) {
            self::$resolvers[$hashKey] = new OptionsResolver();
            $this->configureOptions(self::$resolvers[$hashKey], $classLoader);
        }

        return self::$resolvers[$hashKey]->resolve($options);
    }
}
