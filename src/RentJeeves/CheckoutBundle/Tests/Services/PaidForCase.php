<?php

namespace RentJeeves\CheckoutBundle\Tests\Services;

use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TestBundle\BaseTestCase;

class StartDateCase extends BaseTestCase
{
    /**
     * @test
     */
    public function makeDatesFromDate()
    {
        $paidFor = $this->getMock('RentJeeves\CheckoutBundle\Services\PaidFor', array('getNow'), array(), '', false);
        $paidFor->expects($this->once())
            ->method('getNow')
            ->will($this->returnValue(new DateTime('2014-05-05')));
        $dateTime = new DateTime('2014-02-10');
        $paidTo = clone $dateTime;
        $this->assertEquals(
            $paidFor->createItem($dateTime) +
            $paidFor->createItem($dateTime->modify('+1 month')) +
            $paidFor->createItem($dateTime->modify('+1 month')) +
            $paidFor->createItem($dateTime->modify('+1 month')),
            $this->callNoPublicMethod($paidFor, 'makeDatesFromDate', array($paidTo))
        );
    }

    /**
     * @test
     */
    public function getArray()
    {
        $this->load(true);
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $paidTo = new DateTime();
        $paidTo->setTime(0, 0, 0);
        $paidTo->modify('-2 months');
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(
            array('status' => ContractStatus::FINISHED, 'rent' => '1500', 'paidTo' => $paidTo)
        );
        $contract->setStatus(ContractStatus::CURRENT);
        $em->persist($contract);
        $em->flush($contract);


        $this->assertInstanceOf('RentJeeves\CoreBundle\DateTime', $contract->getPaidTo());

        $paidFor = $this->getContainer()->get('checkout.paid_for');

        $dateTime = clone $paidTo;
        $this->assertEquals(
            $paidFor->createItem($dateTime) +
            $paidFor->createItem($dateTime->modify('+2 month')) +
            $paidFor->createItem($dateTime->modify('+1 month')),
            $paidFor->getArray($contract)
        );

    }
}
