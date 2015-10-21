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
        $paymentAccountId = $paymentAccount->getId();
        $paymentId = $paymentAccount->getPayments()[0]->getId();
        $userSettingId = $paymentAccount->getCreditTrackUserSetting()->getId();
        $this->assertCount(1, $paymentAccount->getHpsMerchants(), 'Payment account should have 1 merchant');

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

        $paymentAccountMerchants = $em->getRepository('RjDataBundle:PaymentAccountHpsMerchant')
            ->findBy(['paymentAccount' => $paymentAccountId]);
        $this->assertCount(0, $paymentAccountMerchants, 'Payment account merchants should be removed');

        /** @var UserSettings $userSetting */
        $userSetting = $em->getRepository('RjDataBundle:UserSettings')
            ->findOneBy(array('id' => $userSettingId));
        $this->assertNotNull($userSetting);
        $this->assertTrue(null == $userSetting->getCreditTrackPaymentAccount());
    }
}
