<?php

namespace RentJeeves\TrustedLandlordBundle\Tests\Functional\Command;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\CheckMailingAddress;
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
        $job =  $this->getEntityManager()->getRepository('RjDataBundle:Job')->findOneBy(
            ['command' => 'renttrack:group:move-mailing-address']
        );
        $this->assertEmpty($job, 'We should NOT found any command with such name');
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
        $group1->setExternalGroupId('te1');

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
        $group2->setExternalGroupId('te2');

        $em->flush();

        $this->executeCommandTester(new MoveDTRGroupAddressToCheckMailingAddressCommand);
        $job =  $this->getEntityManager()->getRepository('RjDataBundle:Job')->findOneBy(
            ['command' => 'renttrack:group:move-mailing-address']
        );
        $this->assertNotEmpty($job, 'We should create new job but we did not');
        $this->assertArrayHasKey(0, $job->getArgs(), 'We should have index 0 which should containts groupsId param');
        $arguments = str_replace('"', '', $job->getArgs()[0]);
        list($key, $value) = explode('=', $arguments);

        $this->executeCommandTester(
            new MoveDTRGroupAddressToCheckMailingAddressCommand,
            [$key => $value]
        );

        /** @var CheckMailingAddress[] $mailingAddress */
        $mailingAddress = $em->getRepository('RjDataBundle:CheckMailingAddress')
            ->findByIndex('771BroadwayNewYorkNY');

        $this->assertEquals(1, count($mailingAddress), 'Only one check mailing address should be created');
        $this->assertEquals(
            $group2->getExternalGroupId(),
            $mailingAddress[0]->getExternalLocationId(),
            'Should be set location_id from external_group_id'
        );
        $this->getEntityManager()->clear();
        /** @var Group $group1 */
        $group1 = $em->getRepository('DataBundle:Group')->find(25);
        /** @var Group $group2 */
        $group2 = $em->getRepository('DataBundle:Group')->find(2);
        $this->assertNotNull($group2->getTrustedLandlord(), 'TrustedLandlord is expected to be set for Group#2');
        $this->assertNull($group1->getTrustedLandlord(), 'TrustedLandlord is expected to be NULL for Group#25');
    }
}
