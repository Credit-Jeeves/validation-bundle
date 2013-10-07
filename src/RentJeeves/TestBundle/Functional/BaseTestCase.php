<?php
namespace RentJeeves\TestBundle\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase as Base;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
abstract class BaseTestCase extends Base
{

    const APP = 'AppRj';

    protected $envPath = '/rj_test.php/';

    public function load($reload = false)
    {
        if (self::$isFixturesLoaded && !$reload) {
            return;
        }

        $container = $this->getContainer();
        $khepin = $container->get('khepin.yaml_loader');

        $khepin->purgeDatabase('orm');
        $khepin->loadFixtures();
        self::$isFixturesLoaded = true;

        $session = $this->getMink()->getSession('goutte');
        $baseUrl = 'http://' . static::getContainer()->getParameter('server_name') . '/test.php/sfPhpunit/';

        $session->visit($baseUrl . 'cc');
    }
}
