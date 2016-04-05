<?php

namespace RentJeeves\TrustedLandlordBundle\Tests\Functional\Command;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\TestBundle\Command\BaseTestCase;
use RentJeeves\TrustedLandlordBundle\Command\MoveDTRGroupAddressToCheckMailingAddressCommand;

class MoveDTRGroupAddressToCheckMailingAddressCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldInsertTrustedLandlordAndCheckMailingAddressForValidAddress()
    {
        $this->load(true);

        $em = $this->getEntityManager();
        /** @var Group $group1 */
        $group1 = $em->getRepository('DataBundle:Group')->find(25);

        //Set valid data
        $group1->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $group1->setMailingAddressName('Jason Waters');
        $group1->setStreetAddress1('771 Broadway'); // this address will be taken from SS cache
        $group1->setStreetAddress2('');
        $group1->setCity('New York');
        $group1->setState('NY');
        $group1->setZip('10003');

        $em->flush();

        /** @var Group $group2 */
        $group2 = $em->getRepository('DataBundle:Group')->find(2);

        //Duplicate address for another entity
        $group2->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $group2->setMailingAddressName('Brian Waters');
        $group2->setStreetAddress1('771 Broadway'); // this address will be taken from SS cache
        $group2->setStreetAddress2('');
        $group2->setCity('New York');
        $group2->setState('NY');
        $group2->setZip('10003');

        $em->flush();

        $this->executeCommandTester(new MoveDTRGroupAddressToCheckMailingAddressCommand);
    
        $mailingAddress = $em->getRepository('RjDataBundle:CheckMailingAddress')
            ->findByIndex('771BroadwayNewYorkNY');

        $this->assertEquals(1, count($mailingAddress), 'Only one check mailing address should be created');

        $this->assertNotNull($group2->getTrustedLandlord(), 'TrustedLandlord is expected to be set for Group#2');
        $this->assertNull($group1->getTrustedLandlord(), 'TrustedLandlord is expected to be NULL for Group#25');
    }
}
