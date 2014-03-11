<?php
namespace CreditJeeves\TestBundle;

use Behat\MinkBundle\Test\MinkTestCase;
use Doctrine\ORM\Tools\SchemaTool;
use \ReflectionClass;
use CreditJeeves\TestBundle\EventListener\EmailListener;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
abstract class BaseTestCase extends MinkTestCase
{
    /**
     * @var string
     */
    const APP = 'AppCj';

    /**
     * 
     * @var boolean
     */
    protected static $isFixturesLoaded = false;

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

    /**
     * @todo check multiple initialization
     */
    protected function registerEmailListener()
    {
        $container = $this->getContainer();
        $mailer = $container->get('mailer');
        $plugin = new EmailListener();
        $mailer->registerPlugin($plugin);
        return $plugin;
    }

    /**
     * Load fixtures
     * This do not need anymore, fixtures load only during build
     *
     * @param bool $reload
     * @return void
     */
    protected function load($reload = false)
    {
        if (self::$isFixturesLoaded && !$reload) {
            return;
        }
        $khepin = $this->getContainer()->get('khepin.yaml_loader');
        $khepin->purgeDatabase('orm');
        $khepin->loadFixtures();
        self::$isFixturesLoaded = true;
        static::$kernel = null;
    }

    protected function startTransaction()
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        foreach ($this->getContainer()->get('doctrine')->getManagers() as $em) {
            $em->clear();
            $em->getConnection()->beginTransaction();
        }
    }

    protected function rollbackTransaction()
    {
        //the error can be thrown during setUp
        //It would be caught by phpunit and tearDown called.
        //In this case we could not rollback since container may not exist.
        if (!$this->getKernel() || !static::$kernel->getContainer()) {
            return;
        }

        /** @var $em \Doctrine\ORM\EntityManager */
        foreach ($this->getContainer()->get('doctrine')->getManagers() as $em) {
            $connection = $em->getConnection();

            while ($connection->isTransactionActive()) {
                $connection->rollback();
            }
        }
    }
}
