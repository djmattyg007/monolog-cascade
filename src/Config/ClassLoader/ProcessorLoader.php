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
namespace MattyG\MonologCascade\Config\ClassLoader;

use MattyG\MonologCascade\Config\ClassLoader;

/**
 * Processor Loader. Loads the Processor options, validate them and instantiates
 * a Processor object (implementing Monolog\Processor\ProcessorInterface) with all
 * the corresponding options
 * @see ClassLoader
 *
 * @author Kate Burdon <kburdon@tableau.com>
 * @author Matthew Gamble
 */
class ProcessorLoader extends ClassLoader
{
    /**
     * @see ClassLoader::__construct
     * @see Monolog\Handler classes for handler options
     *
     * @param array $processorOptions Processor options
     * @param Monolog\Processor\ProcessorInterface[] $processors Array of processors to pick from
     * TODO: Check whether or not $processorOptions needs to be passed by reference
     */
    public function __construct(array &$processorOptions, array $processors = array())
    {
        parent::__construct($processorOptions);

        // @todo add additional options later?  Is the "tags" option needed in this implementation?
    }
}
