<?php
namespace CreditJeeves\TestBundle;

use Behat\MinkBundle\Test\MinkTestCase;
use Doctrine\ORM\Tools\SchemaTool;
use \ReflectionClass;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
abstract class BaseTestCase extends MinkTestCase
{
    const APP = 'AppCj';

    /**
     * {@inheritdoc}
     */
    public function getKernel()
    {
        static $current;
        if ($current != static::APP) {
            $current = static::APP;
            static::$kernel = null;
            static::$class = null;
            $this->setMink();
        }
        return parent::getKernel();
    }

    /**
     * {@inheritdoc}
     */
    protected static function getKernelClass()
    {
        $dir = isset($_SERVER['KERNEL_DIR']) ?
        static::getPhpUnitXmlDir() . '/' . $_SERVER['KERNEL_DIR'] :
        static::getPhpUnitXmlDir();
        require_once $dir . 'AppKernel.php';
        require_once $dir . static::APP . 'Kernel.php';
        require_once $dir . static::APP . 'TestKernel.php';
        return static::APP . 'TestKernel';
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
