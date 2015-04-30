<?php
namespace RentJeeves\DataBundle\Tests\Entity;

use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\UserSettings;
use RentJeeves\DataBundle\Enum\PaymentCloseReason;
use RentJeeves\TestBundle\BaseTestCase;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use Doctrine\ORM\Query\Expr;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 */
class PaymentAccountCase extends BaseTestCase
{
    /**
     * @test
     */
    public function testRemove()
    {
        $this->load(true);

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        /** @var PaymentAccount $paymentAccount */
        $paymentAccount = $em->getRepository('RjDataBundle:PaymentAccount')
            ->createQueryBuilder('pa')
            ->innerJoin('pa.payments', 'p', Expr\Join::WITH, 'p.status = :paymentStatus')
            ->setParameter(':paymentStatus', PaymentStatus::ACTIVE)
            ->innerJoin('pa.creditTrackUserSetting', 'us')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        $this->assertNotNull($paymentAccount);
        $paymentId = $paymentAccount->getPayments()[0]->getId();
        $userSettingId = $paymentAccount->getCreditTrackUserSetting()->getId();
        $depositAccountId = $paymentAccount->getDepositAccounts()[0]->getId();

        foreach ($em->getRepository('RjDataBundle:PaymentAccount')->findAll() as $pa) {
            $em->remove($pa);
            $em->flush($pa);
        }
        static::$kernel = null;
        /** @var Payment $payment */
        $payment = $em->getRepository('RjDataBundle:Payment')->findOneBy(array('id' => $paymentId));
        $this->assertNotNull($payment);
        $this->assertEquals(PaymentStatus::CLOSE, $payment->getStatus());
        $this->assertCount(2, $payment->getCloseDetails());
        $this->assertContains(PaymentCloseReason::DELETED, $payment->getCloseDetails()[1]);
        $today = new DateTime();
        $this->assertEquals($today->format('Y-m-d'), $payment->getUpdatedAt()->format('Y-m-d'));

        /** @var DepositAccount $depositAccount */
        $depositAccount = $em->getRepository('RjDataBundle:DepositAccount')
            ->findOneBy(array('id' => $depositAccountId));
        $this->assertNotNull($depositAccount);

        // FIXME the problem that relation item exists in the DB
        // it works because sofdeleted item did not put to collection
        $this->assertFalse($depositAccount->getPaymentAccounts()->contains($paymentAccount));

        /** @var UserSettings $userSetting */
        $userSetting = $em->getRepository('RjDataBundle:UserSettings')
            ->findOneBy(array('id' => $userSettingId));
        $this->assertNotNull($userSetting);
        $this->assertTrue(null == $userSetting->getCreditTrackPaymentAccount());
    }
}
