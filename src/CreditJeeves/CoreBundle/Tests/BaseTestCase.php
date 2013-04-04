<?php
namespace CreditJeeves\CoreBundle\Tests;

use Behat\MinkBundle\Test\MinkTestCase;
/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
abstract class BaseTestCase extends MinkTestCase
{
    /**
     * {@inheritdoc}
     */
    protected static function getKernelClass()
    {
        $dir = isset($_SERVER['KERNEL_DIR']) ? $_SERVER['KERNEL_DIR'] : static::getPhpUnitXmlDir();
        require_once $dir . 'AppKernel.php';
        require_once $dir . 'AppTestKernel.php';
        return 'AppTestKernel';
    }
}
