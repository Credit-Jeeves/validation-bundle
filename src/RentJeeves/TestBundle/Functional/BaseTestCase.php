<?php
namespace RentJeeves\TestBundle\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase as Base;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
abstract class BaseTestCase extends Base
{

    const APP = 'AppRj';

    public function load($reload = false)
    {
        if (self::$isFixturesLoaded && !$reload) {
            return;
        }

        //@TODO: enviroment not see rj fixuture when running all test and see it if rj test only run. Need Fix it.
        $kernel = static::getContainer()->get('kernel');
        $env = static::getContainer()->get('kernel')->getEnvironment();
        $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
        $application->setAutoExit(false); 
        $options = array('command' => 'khepin:yamlfixtures:load', '--app' =>'rj', '--env'=>$env);
        
        if ($reload) {
            $options['--purge-orm'] = true;
        }
        
        self::$isFixturesLoaded = true;
        $application->run(new \Symfony\Component\Console\Input\ArrayInput($options));
/*        $khepin = static::getContainer()->get('khepin.yaml_loader');

        if ($reload) {
            $khepin->purgeDatabase('orm');
        }
        $khepin->loadFixtures();
        self::$isFixturesLoaded = true;*/

        $session = $this->getMink()->getSession('goutte');
        $baseUrl = 'http://' . static::getContainer()->getParameter('server_name') . '/test.php/sfPhpunit/';

        $session->visit($baseUrl . 'cc');
    }
}
