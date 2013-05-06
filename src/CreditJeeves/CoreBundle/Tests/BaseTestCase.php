<?php
namespace CreditJeeves\CoreBundle\Tests;

use Behat\MinkBundle\Test\MinkTestCase;
use \ReflectionClass;

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

    /**
     * Open no public methods
     *
     * @param string $obj
     * @param string $methodName
     * @param array $args
     *
     * @return mixed
     */
    protected function callNoPublicMethod($obj, $methodName, array $args)
    {
        $class = new ReflectionClass($obj);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }
}
