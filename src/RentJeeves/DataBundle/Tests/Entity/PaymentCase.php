<?php
namespace RentJeeves\DataBundle\Tests\Entity;

use RentJeeves\DataBundle\Enum\PaymentCloseReason;
use RentJeeves\TestBundle\BaseTestCase;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use Doctrine\ORM\Query\Expr;
use RentJeeves\CoreBundle\DateTime;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 */
class PaymentCase extends BaseTestCase
{
    public function providerForgetNextPaymentDate()
    {
        return array(
            array('2014-11-01', 1, '2014-10-01', '2014-11-01'),
            array('2014-01-01', 31, '2014-01-15', '2014-01-31'),
            array('2014-01-01', 30, '2014-02-27', '2014-02-28'),
            array('2014-01-01', 1, '2014-02-27', '2014-03-01'),
            array('2014-01-01', 1, '2014-03-31', '2014-04-01'),
            array('2025-01-09', 9, '2015-05-07', '2025-01-09'),
        );
    }

    public function getNextPaymentDate($startDate, $dueDate, $now, $will)
    {
        /** @var Payment $payment */
        $payment = $this->getMock(
            '\RentJeeves\DataBundle\Entity\Payment',
            array('getNow'),
            array(),
            '',
            false
        );
        $payment->setStartDate($startDate);
        $payment->expects($this->any())
            ->method('getNow')
            ->will($this->returnValue(new DateTime($now)));
        $payment->setDueDate($dueDate);

        $this->assertEquals(new DateTime($will), $payment->getNextPaymentDate());
    }

    /**
     * @test
     * @dataProvider providerForgetNextPaymentDate
     */
    public function getNextPaymentDateWithDifferentTimezones($startDate, $dueDate, $now, $will)
    {
        date_default_timezone_set('Europe/Kiev');
        $this->getNextPaymentDate($startDate, $dueDate, $now, $will);
        date_default_timezone_set('America/New_York');
        $this->getNextPaymentDate($startDate, $dueDate, $now, $will);
        date_default_timezone_set('GMT');
        $this->getNextPaymentDate($startDate, $dueDate, $now, $will);
    }

    /**
     * @test
     */
    public function prePersist()
    {
        $this->load(true);
        $doctrineManager = $this->getContainer()->get('doctrine')->getManager();
        /** @var Contract $contract */
        $contract = $doctrineManager->getRepository('RjDataBundle:Contract')
            ->createQueryBuilder('c')
            ->innerJoin('c.payments', 'p', Expr\Join::WITH, 'p.status = :paymentStatus')
            ->setParameter(':paymentStatus', PaymentStatus::ACTIVE)
            ->andWhere('c.status = :contractStatus')
            ->setParameter('contractStatus', ContractStatus::APPROVED)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        $this->assertNotNull($contract);
        $this->assertNotNull($payment = $contract->getActiveRentPayment());
        $paymentId = $payment->getId();
        $this->assertEquals(PaymentStatus::ACTIVE, $payment->getStatus());

        $contract->setStatus(ContractStatus::DELETED);
        $doctrineManager->persist($contract);
        $doctrineManager->flush($contract);
        static::$kernel = null;
        /** @var Payment $payment */
        $payment = $doctrineManager->getRepository('RjDataBundle:Payment')->findOneBy(array('id' => $paymentId));
        $this->assertNotNull($payment);
        $this->assertCount(2, $payment->getCloseDetails());
        $this->assertContains(PaymentCloseReason::CONTRACT_DELETED, $payment->getCloseDetails()[1]);
        $this->assertEquals(PaymentStatus::CLOSE, $payment->getStatus());
        $today = new DateTime();
        $this->assertEquals($today->format('Y-m-d'), $payment->getUpdatedAt()->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function shouldSetCloseReasonWhenClosePayment()
    {
        $payment = new Payment();
        $payment->setClosed($this, PaymentCloseReason::CONTRACT_CHANGED);

        $this->assertEquals(PaymentStatus::CLOSE, $payment->getStatus());
        $this->assertCount(2, $payment->getCloseDetails());
        $this->assertEquals('Class: ' . get_class($this), $payment->getCloseDetails()[0]);
        $this->assertEquals('Reason: ' . PaymentCloseReason::CONTRACT_CHANGED, $payment->getCloseDetails()[1]);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage
     */
    public function shouldThrowAnExceptionWhenCallerIsNotAnObject()
    {
        $payment = new Payment();
        $payment->setClosed([], PaymentCloseReason::CONTRACT_CHANGED);
    }
}
