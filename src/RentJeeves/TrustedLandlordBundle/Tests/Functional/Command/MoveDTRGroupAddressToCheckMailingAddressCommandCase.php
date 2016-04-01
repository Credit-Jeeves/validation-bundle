<?php

namespace RentJeeves\TrustedLandlordBundle\Tests\Functional\Command;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\TestBundle\Command\BaseTestCase;
use RentJeeves\TrustedLandlordBundle\Command\MoveDTRGroupAddressToCheckMailingAddressCommand;
use Symfony\Component\Console\Tester\CommandTester;

class MoveDTRGroupAddressToCheckMailingAddressCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCheckMailingAddressCountIsZeroIfNoGroupsPayDirect()
    {
        $this->load(true);

        $this->executeCommandTester(new MoveDTRGroupAddressToCheckMailingAddressCommand);

        $mailingAddresses = $this->getEntityManager()->getRepository('RjDataBundle:CheckMailingAddress')->findAll();

        $this->assertEquals(0, count($mailingAddresses), 'Mailing addresses should be empty');
    }

    /**
     * @test
     */
    public function shouldInsertTrustedLandlordAndCheckMailingAddressForValidAddress()
    {
        $this->load(true);

        $em = $this->getEntityManager();
        /** @var $group1 Group */
        $group1 = $em->getRepository('DataBundle:Group')->find(25);

        //Set valid data
        $group1->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $group1->setMailingAddressName('Jason Waters');
        $group1->setStreetAddress1('1600 Amphitheatre Pkwy');
        $group1->setStreetAddress2('');
        $group1->setCity('Mountain View');
        $group1->setState('CA');
        $group1->setZip('94043');

        /** @var $group2 Group */
        $group2 = $em->getRepository('DataBundle:Group')->find(2);

        //Duplicate address for another entity
        $group2->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $group2->setMailingAddressName('Brian Waters');
        $group2->setStreetAddress1('1600 Amphitheatre Pkwy');
        $group2->setStreetAddress2('');
        $group2->setCity('Mountain View');
        $group2->setState('CA');
        $group2->setZip('94043');

        $em->flush();

        $this->executeCommandTester(new MoveDTRGroupAddressToCheckMailingAddressCommand);
    
        $mailingAddress = $em->getRepository('RjDataBundle:CheckMailingAddress')
            ->findByIndex('1600AmphitheatrePkwyMountainViewCA');

        $this->assertEquals(1, count($mailingAddress), 'Only one check mailing address should be created');

        $this->assertNotNull($group2->getTrustedLandlord(), 'TrustedLandlord is expected to be set for Group#2');
        $this->assertNull($group1->getTrustedLandlord(), 'TrustedLandlord is expected to be NULL for Group#25');
    }
}
