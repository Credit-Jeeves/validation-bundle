<?php
namespace CreditJeeves\CoreBundle\Tests;

use Behat\MinkBundle\Test\MinkTestCase;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
abstract class BaseTestCase extends MinkTestCase
{
    /**
     * {@inheritdoc}
     */
    protected static function getKernelClass()
    {
        $dir = isset($_SERVER['KERNEL_DIR']) ?
        static::getPhpUnitXmlDir() . '/' . $_SERVER['KERNEL_DIR'] :
        static::getPhpUnitXmlDir();
        require_once $dir . 'AppKernel.php';
        require_once $dir . 'AppTestKernel.php';
        return 'AppTestKernel';
    }
}
