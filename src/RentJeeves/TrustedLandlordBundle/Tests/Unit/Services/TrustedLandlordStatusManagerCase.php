<?php

namespace RentJeeves\TrustedLandlordBundle\Tests\Unit\Services;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\Common\Collections\ArrayCollection;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\TrustedLandlord;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\TrustedLandlordStatus;
use RentJeeves\TrustedLandlordBundle\Services\TrustedLandlordStatusManager;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\DataBundle\Entity\Job;

class TrustedLandlordStatusManagerCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     */
    public function shouldHandleFailedStatusAndLogAlertAboutIt()
    {
        $trustedLandlord = new TrustedLandlord();
        $trustedLandlord->setStatus(TrustedLandlordStatus::NEWONE);
        $now = new \DateTime('+30 minutes');

        $trustedLandlordStatusManager = new TrustedLandlordStatusManager(
            $this->getEntityManagerMock(),
            $logger = $this->getLoggerMock(),
            $this->getMailerMock(),
            $now->format('H:m'),
            $now->format('H:m')
        );

        $logger->expects($this->once())
            ->method('alert');

        $result = $trustedLandlordStatusManager->updateStatus($trustedLandlord, TrustedLandlordStatus::FAILED);
        $this->assertTrue($result, 'Doesn\'t update status');
        $this->assertEquals(
            $trustedLandlord->getStatus(),
            TrustedLandlordStatus::FAILED,
            'We don\'t update status'
        );
    }

    /**
     * @test
     */
    public function shouldHandleInProgressStatusShouldLogAboutIt()
    {
        $trustedLandlord = new TrustedLandlord();
        $trustedLandlord->setStatus(TrustedLandlordStatus::NEWONE);
        $now = new \DateTime('+30 minutes');

        $trustedLandlordStatusManager = new TrustedLandlordStatusManager(
            $this->getEntityManagerMock(),
            $logger = $this->getLoggerMock(),
            $this->getMailerMock(),
            $now->format('H:m'),
            $now->format('H:m')
        );

        $logger->expects($this->once())
            ->method('debug');

        $result = $trustedLandlordStatusManager->updateStatus($trustedLandlord, TrustedLandlordStatus::IN_PROGRESS);
        $this->assertTrue($result, 'Doesn\'t update status');
        $this->assertEquals(
            $trustedLandlord->getStatus(),
            TrustedLandlordStatus::IN_PROGRESS,
            'We don\'t update status'
        );
    }

    /**
     * @test
     */
    public function shouldHandleNewStatusAndCreateJob()
    {
        $trustedLandlord = new TrustedLandlord();
        $now = new \DateTime('+30 minutes');

        $trustedLandlordStatusManager = new TrustedLandlordStatusManager(
            $em = $this->getEntityManagerMock(),
            $this->getLoggerMock(),
            $this->getMailerMock(),
            $now->format('H:m'),
            $now->format('H:m')
        );

        $em->expects($this->once())
            ->method('persist')
            ->with($this->callback(
                function ($job) {
                    return $job instanceof Job;
                }
            ));

        $em->expects($this->exactly(2))
            ->method('flush');

        $result = $trustedLandlordStatusManager->updateStatus($trustedLandlord, TrustedLandlordStatus::NEWONE);
        $this->assertTrue($result, 'Doesn\'t update status');
        $this->assertEquals($trustedLandlord->getStatus(), TrustedLandlordStatus::NEWONE, 'We don\'t update status');
    }

    /**
     * @test
     */
    public function shouldReturnFalseWhenInvalidStatusPassed()
    {
        $trustedLandlord = new TrustedLandlord();
        $now = new \DateTime('+30 minutes');

        $trustedLandlordStatusManager = new TrustedLandlordStatusManager(
            $this->getEntityManagerMock(),
            $logger = $this->getLoggerMock(),
            $this->getMailerMock(),
            $now->format('H:m'),
            $now->format('H:m')
        );

        $logger->expects($this->once())
            ->method('debug');

        $result = $trustedLandlordStatusManager->updateStatus($trustedLandlord, 'bla bla bla');
        $this->assertFalse($result, 'Successfully update status');
    }

    /**
     * @test
     */
    public function shouldDoNotUpdateStatusIfStatusTheSame()
    {
        $trustedLandlord = new TrustedLandlord();
        $trustedLandlord->setStatus(TrustedLandlordStatus::WAITING_FOR_INFO);
        $now = new \DateTime('+30 minutes');

        $trustedLandlordStatusManager = new TrustedLandlordStatusManager(
            $this->getEntityManagerMock(),
            $logger = $this->getLoggerMock(),
            $this->getMailerMock(),
            $now->format('H:m'),
            $now->format('H:m')
        );

        $logger->expects($this->once())
            ->method('debug');

        $result = $trustedLandlordStatusManager->updateStatus(
            $trustedLandlord,
            TrustedLandlordStatus::WAITING_FOR_INFO
        );
        $this->assertFalse($result, 'Successfully update status');
        $this->assertEquals($trustedLandlord->getStatus(), TrustedLandlordStatus::WAITING_FOR_INFO, 'We update status');
    }

    /**
     * @test
     * @expectedException RentJeeves\TrustedLandlordBundle\Exception\TrustedLandlordStatusException
     */
    public function shouldBlockToChangeTrustedStatus()
    {
        $trustedLandlord = new TrustedLandlord();
        $trustedLandlord->setStatus(TrustedLandlordStatus::TRUSTED);
        $now = new \DateTime('+30 minutes');

        $trustedLandlordStatusManager = new TrustedLandlordStatusManager(
            $this->getEntityManagerMock(),
            $logger = $this->getLoggerMock(),
            $this->getMailerMock(),
            $now->format('H:m'),
            $now->format('H:m')
        );

        $logger->expects($this->once())
            ->method('alert');

        $trustedLandlordStatusManager->updateStatus($trustedLandlord, TrustedLandlordStatus::NEWONE);
    }

    /**
     * @test
     * @expectedException RentJeeves\TrustedLandlordBundle\Exception\TrustedLandlordStatusException
     */
    public function shouldBlockToChangeDeniedStatus()
    {
        $trustedLandlord = new TrustedLandlord();
        $trustedLandlord->setStatus(TrustedLandlordStatus::DENIED);
        $now = new \DateTime('+30 minutes');

        $trustedLandlordStatusManager = new TrustedLandlordStatusManager(
            $this->getEntityManagerMock(),
            $logger = $this->getLoggerMock(),
            $this->getMailerMock(),
            $now->format('H:m'),
            $now->format('H:m')
        );

        $logger->expects($this->once())
            ->method('alert');

        $trustedLandlordStatusManager->updateStatus($trustedLandlord, TrustedLandlordStatus::NEWONE);
    }

    /**
     * @test
     */
    public function shouldSetTrustedStatusAndCheckStartAtOfPayments()
    {
        $trustedLandlord = new TrustedLandlord();
        $trustedLandlord->setStatus(TrustedLandlordStatus::IN_PROGRESS);
        $trustedLandlord->setGroup(new Group());
        $now = new \DateTime('-30 minutes');

        $trustedLandlordStatusManager = new TrustedLandlordStatusManager(
            $em = $this->getEntityManagerMock(),
            $logger = $this->getLoggerMock(),
            $mailer = $this->getMailerMock(),
            $now->format('H:m'),
            $now->format('H:m')
        );

        $paymentDebit = new Payment();
        $accountDebit = new PaymentAccount();
        $paymentDebit->setStartDate('now');
        $accountDebit->setType(PaymentAccountType::DEBIT_CARD);
        $paymentCredit = new Payment();
        $accountCredit = new PaymentAccount();
        $paymentCredit->setStartDate('now');
        $accountCredit->setType(PaymentAccountType::CARD);
        $paymentCredit->setPaymentAccount($accountCredit);
        $paymentDebit->setPaymentAccount($accountDebit);
        $arrayCollection = new ArrayCollection();
        $arrayCollection->add($paymentDebit);
        $arrayCollection->add($paymentCredit);

        $repository = $this->getBaseMock('RentJeeves\DataBundle\Entity\PaymentRepository');

        $repository->expects($this->once())
            ->method('findAllFlaggedPaymentToUntrustedLandlord')
            ->with($this->callback(function ($group) {
                return $group instanceof Group;
            }))
            ->willReturn($arrayCollection);

        $em->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);

        $em->expects($this->exactly(2))
            ->method('flush');

        $mailer->expects($this->exactly(2))
            ->method('sendTrustedLandlordApproved')
            ->with(
                $this->callback(function ($payment) {
                    return $payment instanceof Payment;
                })
            );

        $result = $trustedLandlordStatusManager->updateStatus($trustedLandlord, TrustedLandlordStatus::TRUSTED);
        $this->assertTrue($result, 'Status doesn\'t update.');
        $tomorrow = new \DateTime('now');
        $this->assertEquals(
            $tomorrow->format('dmY'),
            $paymentCredit->getStartDate()->format('dmY'),
            'Start date of payment credit don\'t set to tomorrow'
        );
        $this->assertEquals(
            $tomorrow->format('dmY'),
            $paymentDebit->getStartDate()->format('dmY'),
            'Start date of payment debit don\'t set to tomorrow'
        );
        $this->assertEquals($trustedLandlord->getStatus(), TrustedLandlordStatus::TRUSTED, 'Status didn\'t update');
    }

    /**
     * @test
     */
    public function shouldSetDeniedStatusAndCloseActivePayments()
    {
        $trustedLandlord = new TrustedLandlord();
        $trustedLandlord->setGroup(new Group());
        $trustedLandlord->setStatus(TrustedLandlordStatus::IN_PROGRESS);
        $now = new \DateTime('+30 minutes');

        $trustedLandlordStatusManager = new TrustedLandlordStatusManager(
            $em = $this->getEntityManagerMock(),
            $this->getLoggerMock(),
            $mailer = $this->getMailerMock(),
            $now->format('H:m'),
            $now->format('H:m')
        );

        $payment = new Payment();
        $payment->setStatus(PaymentStatus::ACTIVE);
        $payment->setStartDate('now');
        $arrayCollection = new ArrayCollection();
        $arrayCollection->add($payment);
        $repository = $this->getBaseMock('RentJeeves\DataBundle\Entity\PaymentRepository');

        $repository->expects($this->once())
            ->method('findAllActiveAndFlaggedPaymentsForGroup')
            ->with($this->callback(function ($group) {
                return $group instanceof Group;
            }))
            ->willReturn($arrayCollection);

        $em->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);

        $em->expects($this->exactly(2))
            ->method('flush');

        $mailer->expects($this->once())
            ->method('sendTrustedLandlordDenied')
            ->with(
                $this->callback(function ($payment) {
                    return $payment instanceof Payment;
                })
            );

        $result = $trustedLandlordStatusManager->updateStatus($trustedLandlord, TrustedLandlordStatus::DENIED);
        $this->assertTrue($result, 'Status doesn\'t update.');
        $this->assertEquals(
            PaymentStatus::CLOSE,
            $payment->getStatus(),
            'Payment status doesn\'t update correctly'
        );
        $this->assertArrayHasKey(1, $details = $payment->getCloseDetails(), 'We don\'t set details');
        $this->assertEquals(
            $details[1],
            'Reason: We were unable to verify your Property Manager',
            'Payment closeDetail doesn\'t update correctly'
        );
        $this->assertEquals($trustedLandlord->getStatus(), TrustedLandlordStatus::DENIED, 'Status didn\'t update');
    }
}
