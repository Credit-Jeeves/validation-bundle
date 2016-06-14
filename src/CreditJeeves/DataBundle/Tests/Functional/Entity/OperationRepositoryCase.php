<?php

namespace CreditJeeves\DataBundle\Tests\Functional\Entity;

use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\TestBundle\BaseTestCase;

class OperationRepositoryCase extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->load(true);

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();

        $qb->update('DataBundle:Order', 'o')
            ->set('o.created_at', $qb->expr()->literal('2001-01-01 00:00:00'))
            ->getQuery()
            ->execute();
    }

    /**
     * @test
     */
    public function shouldCalculateSumOfPaymentsInMonthForDateByGroup()
    {
        $em = $this->getEntityManager();
        $group24 = $em->find('DataBundle:Group', 24);
        $this->assertNotNull($group24, 'Check fixtures, should exist group #24');
        $operationRepo = $em->getRepository('DataBundle:Operation');
        $this->assertEquals(
            0,
            $operationRepo->getSumPaymentsByGroupInDateMonth($group24, new \DateTime('2016-02-26 16:58:01')),
            'Sum should be 0 b/c we do not have any orders for this date month'
        );
        $operationsForContract9 = $em->getRepository('DataBundle:Operation')->findBy(['contract' => 9]);
        $this->assertCount(12, $operationsForContract9, 'Check fixtures should be exist 12 for contract #9');
        $operationsForContract7 = $em->getRepository('DataBundle:Operation')->findBy(['contract' => 7]);
        $this->assertCount(1, $operationsForContract7, 'Check fixtures should be exist 1 for contract #7');

        // check first and last time of period and diff contract but belongs one group
        $operationsForContract7[0]->getOrder()->setCreatedAt(new \DateTime('2016-02-10 10:01:12'));
        $operationsForContract7[0]->getOrder()->setStatus(OrderStatus::COMPLETE);
        $operationsForContract7[0]->setAmount(100); // should be included
        $operationsForContract9[0]->getOrder()->setCreatedAt(new \DateTime('2016-02-01 00:00:00'));
        $operationsForContract9[0]->getOrder()->setStatus(OrderStatus::COMPLETE);
        $operationsForContract9[0]->setAmount(100); // should be included
        $operationsForContract9[1]->getOrder()->setCreatedAt(new \DateTime('2016-02-29 23:59:59'));
        $operationsForContract9[1]->setAmount(100); // should be included
        $operationsForContract9[1]->getOrder()->setStatus(OrderStatus::COMPLETE);
        $operationsForContract9[2]->setAmount(100); // should be included the same order
        $operationsForContract9[3]->getOrder()->setCreatedAt(new \DateTime('2016-01-31 23:59:59'));
        $operationsForContract9[3]->getOrder()->setStatus(OrderStatus::COMPLETE);
        $operationsForContract9[3]->setAmount(100); // should not be included
        $operationsForContract9[4]->getOrder()->setCreatedAt(new \DateTime('2016-03-01 00:00:00'));
        $operationsForContract9[4]->getOrder()->setStatus(OrderStatus::COMPLETE);
        $operationsForContract9[4]->setAmount(100); // should not be included
        $operationsForContract9[5]->getOrder()->setCreatedAt(new \DateTime('2016-02-01 00:00:01'));
        $operationsForContract9[5]->getOrder()->setStatus(OrderStatus::COMPLETE);
        $operationsForContract9[5]->setAmount(100); // should be included

        $em->flush();

        $this->assertEquals(
            500,
            $operationRepo->getSumPaymentsByGroupInDateMonth($group24, new \DateTime('2016-02-26 16:58:02')),
            'Sum should be 500 we have 5 operations that belongs to contracts with group#24' .
            ' and has completed orders that was created date in February of 2016'
        );

        // check that choose just one group
        $group25 = $em->find('DataBundle:Group', 25);
        $this->assertNotNull($group24, 'Check fixtures, should exist group #25');

        $operationsForContract7[0]->getOrder()->getContract()->setGroup($group25);

        $em->flush();

        $this->assertEquals(
            400,
            $operationRepo->getSumPaymentsByGroupInDateMonth($group24, new \DateTime('2016-02-26 16:58:03')),
            'Sum should be 400 we have just 4 operations that belongs to contracts with group#24' .
            ' and has completed orders that was created date in February of 2016'
        );

        // check that we choose all success order statuses
        $operationsForContract7[0]->getOrder()->getContract()->setGroup($group24);
        $operationsForContract9[0]->getOrder()->setStatus(OrderStatus::PENDING);
        $operationsForContract9[1]->getOrder()->setStatus(OrderStatus::REISSUED);
        $operationsForContract9[5]->getOrder()->setStatus(OrderStatus::SENDING);

        $em->flush();

        // check that we choose just rent, other and custom operation
        $operationsForContract7[0]->setType(OperationType::REPORT);
        $operationsForContract9[0]->setType(OperationType::CHARGE);
        $operationsForContract9[1]->setType(OperationType::CUSTOM);
        $operationsForContract9[2]->setType(OperationType::OTHER);
        $operationsForContract9[5]->setType(OperationType::RENT);

        $em->flush();

        $this->assertEquals(
            300,
            $operationRepo->getSumPaymentsByGroupInDateMonth($group24, new \DateTime('2016-02-26 16:58:04')),
            'Sum should be 300 we have 3 correct operations that belongs to contracts with group#24' .
            ' and has successfull orders that was created date in February of 2016'
        );

        // check that all other statuses do not work
        $operationsForContract7[0]->getOrder()->setStatus(OrderStatus::NEWONE);
        $operationsForContract7[0]->setType(OperationType::RENT);
        $operationsForContract9[0]->getOrder()->setStatus(OrderStatus::CANCELLED);
        $operationsForContract9[0]->setType(OperationType::RENT);
        $operationsForContract9[1]->getOrder()->setStatus(OrderStatus::ERROR);
        $operationsForContract9[1]->setType(OperationType::RENT);
        $operationsForContract9[2]->setType(OperationType::RENT);
        $operationsForContract9[3]->getOrder()->setCreatedAt(new \DateTime('2016-02-11 10:00:00'));
        $operationsForContract9[3]->getOrder()->setStatus(OrderStatus::RETURNED);
        $operationsForContract9[3]->setType(OperationType::RENT);
        $operationsForContract9[4]->getOrder()->setCreatedAt(new \DateTime('2016-02-10 10:00:00'));
        $operationsForContract9[4]->getOrder()->setStatus(OrderStatus::REFUNDING);
        $operationsForContract9[4]->setType(OperationType::RENT);
        $operationsForContract9[5]->getOrder()->setStatus(OrderStatus::REFUNDED);
        $operationsForContract9[5]->setType(OperationType::RENT);

        $em->flush();

        $this->assertEquals(
            0,
            $operationRepo->getSumPaymentsByGroupInDateMonth($group24, new \DateTime('2016-02-26 16:58:05')),
            'Sum should be 0 b/c we do not have any success orders that was created date in February of 2016'
        );
    }
}
