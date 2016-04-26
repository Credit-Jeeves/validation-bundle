<?php

namespace CreditJeeves\CoreBundle\Tests\Unit\Mailer;

use CreditJeeves\CoreBundle\Mailer\MailAuthorizer;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class MailAuthorizerCase extends UnitTestBase
{
    /**
     * @test
     * @dataProvider westsideParterWhitelist
     */
    public function shouldAllowWestsideWhitelist($emailTemplateName)
    {
        $user = $this->getWestsideUserMock();
        $isAllowed = MailAuthorizer::isAllowed($emailTemplateName, $user);
        $this->assertTrue(
            $isAllowed,
            sprintf('Email template %s should be allowed for westside users', $emailTemplateName)
        );
    }

    /**
     * @test
     * @dataProvider westsideParterBlacklist
     */
    public function shouldNotAllowWestsideBlacklist($emailTemplateName)
    {
        $user = $this->getWestsideUserMock();
        $isAllowed = MailAuthorizer::isAllowed($emailTemplateName, $user);
        $this->assertFalse(
            $isAllowed,
            sprintf('Email template %s should NOT be allowed for westside users', $emailTemplateName)
        );
    }

    /**
     * @test
     * @dataProvider allEmailTemplateNames
     */
    public function shouldAllowAllEmailsForRentTrackUser($emailTemplateName)
    {
        $user = $this->getRentTrackUserMock();
        $isAllowed = MailAuthorizer::isAllowed($emailTemplateName, $user);
        $this->assertTrue(
            $isAllowed,
            sprintf('Email template %s should be allowed for non-westside users', $emailTemplateName)
        );
    }

    /**
     * @test
     * @dataProvider allEmailTemplateNames
     */
    public function shouldNotAllowAnyEmailsForDisabledNotificationUser($emailTemplateName)
    {
        $user = $this->getRentTrackUserMock(false);
        $isAllowed = MailAuthorizer::isAllowed($emailTemplateName, $user);
        $this->assertFalse(
            $isAllowed,
            sprintf('Email template %s should NOT be allowed for users with EmailNotification = 0 ', $emailTemplateName)
        );
    }

    /**
     * @test
     * @dataProvider westsideParterWhitelist
     */
    public function shouldNotAllowWestsideWhitelistForDisabledNotificationUser($emailTemplateName)
    {
        $user = $this->getWestsideUserMock(false);
        $isAllowed = MailAuthorizer::isAllowed($emailTemplateName, $user);
        $this->assertFalse(
            $isAllowed,
            sprintf('Email template %s should be allowed for westside users', $emailTemplateName)
        );
    }

    public function westsideParterWhitelist()
    {
        return [
            ["rjOrderCancel"],
            ["rjOrderError"],
            ["rjOrderReceipt"],
            ["rjOrderRefunding"],
            ["rjOrderReissued"],
            ["rjOrderSending"],
            ["rjPaymentDue"],
            ["rjPendingOrder"],
            ["rjTrustedLandlordDenied"],
            ["rjTrustedLandlordApproved"],
            ["rjPaymentFlaggedByUntrustedLandlordRule"]
        ];
    }

    public function westsideParterBlacklist()
    {
        return [
            ["check"],
            ["example"],
            ["exist_invite"],
            ["finished"],
            ["invite"],
            ["password"],
            ["receipt"],
            ["resetting"],
            ["rj_resetting"],
            ["rjBatchDepositReportHolding"],
            ["rjBatchDepositReportLandlord"],
            ["rjCheck"],
            ["rjChurnRecapture"],
            ["rjContractAmountChanged"],
            ["rjContractApproved"],
            ["rjContractRemovedFromDbByLandlord"],
            ["rjContractRemovedFromDbByTenant"],
            ["rjDailyReport"],
            ["rjEndContract"],
            ["rjFreeReportUpdated"],
            ["rjLandlordComeFromInvite"],
            ["rjLandLordInvite"],
            ["rjListLateContracts"],
            ["rjMerchantNameSetuped"],
            ["rjOrderCancelToLandlord"],
            ["rjPendingContract"],
            ["rjPostPaymentError"],
            ["rjPushBatchReceiptsReport"],
            ["rjReceipt"],
            ["rjScoreTrackOrderError"],
            ["rjSecondChanceForContract"],
            ["rjTenantInvite"],
            ["rjTenantInviteReminder"],
            ["rjTenantLateContract"],
            ["rjTenantLatePayment"],
            ["rjTodayNotPaid"],
            ["rjTodayPayments"],
            ["rjYardiPaymentAcceptedTurnOff"],
            ["rjYardiPaymentAcceptedTurnOn"],
            ["score"],
            ["target"],
            ["welcome"]
        ];
    }

    public function allEmailTemplateNames()
    {
        return array_merge($this->westsideParterWhitelist(), $this->westsideParterBlacklist());
    }

    // public function shouldNotAllowEmailsOutsideWestsideWhitelist()

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\DataBundle\Entity\Partner
     */
    public function getPartnerMock($requestName)
    {
        $mockObj = $this->getMock('\RentJeeves\DataBundle\Entity\Partner', ['getRequestName'], [], '', false);
        $mockObj->method('getRequestName')
            ->will($this->returnValue($requestName));

        return $mockObj;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\CreditJeeves\DataBundle\Entity\User
     */
    public function getWestsideUserMock($emailNotificationEnabled = true)
    {
        $mockObj = $this->getMock(
            '\CreditJeeves\DataBundle\Entity\User',
            ['getPartner', 'getEmailNotification'],
            [],
            '',
            false
        );
        $mockObj->method('getPartner')
            ->will($this->returnValue($this->getPartnerMock("WESTSIDE")));
        $mockObj->method('getEmailNotification')
            ->will($this->returnValue($emailNotificationEnabled));

        return $mockObj;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\CreditJeeves\DataBundle\Entity\User
     */
    public function getRentTrackUserMock($emailNotificationEnabled = true)
    {
        $mockObj = $this->getMock(
            '\CreditJeeves\DataBundle\Entity\User',
            ['getPartner', 'getEmailNotification'],
            [],
            '',
            false
        );
        $mockObj->method('getPartner')
            ->will($this->returnValue(null));
        $mockObj->method('getEmailNotification')
            ->will($this->returnValue($emailNotificationEnabled));

        return $mockObj;
    }
}
