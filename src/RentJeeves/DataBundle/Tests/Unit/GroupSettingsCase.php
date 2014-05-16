<?php

namespace RentJeeves\DataBundle\Tests\Unit;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\GroupSettings;
use RentJeeves\TestBundle\BaseTestCase;

class GroupSettingsCase extends BaseTestCase
{
    /**
     * @expectedException Symfony\Component\Form\Exception\LogicException
     * @test
     */
    public function makeSureIntegratedFieldWillNotChangeFromTrueToFalse()
    {
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /**
         * @var $groupSetting GroupSettings
         */
        $groupSetting = $em->getRepository('RjDataBundle:GroupSettings')->findOneBy(
            array(
                'isIntegrated' => true
            )
        );
        $this->assertNotNull($groupSetting);
        $groupSetting->setIsIntegrated(false);
        $em->persist($groupSetting);
        $em->flush();
    }
}
