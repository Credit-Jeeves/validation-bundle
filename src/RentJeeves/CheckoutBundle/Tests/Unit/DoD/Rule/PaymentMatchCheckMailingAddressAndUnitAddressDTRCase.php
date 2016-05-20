<?php
namespace RentJeeves\CheckoutBundle\Tests\Unit\DoD\Rule;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CheckoutBundle\DoD\Rule\PaymentMatchCheckMailingAddressAndUnitAddressDTR;
use RentJeeves\DataBundle\Entity\CheckMailingAddress;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyAddress;
use RentJeeves\DataBundle\Entity\TrustedLandlord;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\PaymentFlaggedReason;
use RentJeeves\DataBundle\Enum\TrustedLandlordStatus;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class PaymentMatchCheckMailingAddressAndUnitAddressDTRCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     */
    public function shouldReturnTrueIfGroupHasOrderAlgorithmTypeNotPayDirect()
    {
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::SUBMERCHANT);
        $contract = new Contract();
        $contract->setGroup($group);
        $payment = new Payment();
        $payment->setContract($contract);
        $paymentRule = new PaymentMatchCheckMailingAddressAndUnitAddressDTR();
        $this->assertTrue(
            $paymentRule->checkPayment($payment),
            'Should always return true if payment\'s group has order algorithm not "pay_direct".'
        );
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfGroupDoesNotHaveTrustedLandlord()
    {
        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $contract = new Contract();
        $contract->setGroup($group);
        $payment = new Payment();
        $payment->setContract($contract);

        $paymentRule = new PaymentMatchCheckMailingAddressAndUnitAddressDTR();
        $this->assertTrue(
            $paymentRule->checkPayment($payment),
            'Should return true if payment\'s group dtr and does not have trusted landlord.'
        );
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfPaymentHasDifferentMailingAddressAndUnitAddress()
    {
        $checkMailingAddress = new CheckMailingAddress();
        $checkMailingAddress->setIndex('770BroadwayNewYorkNY');

        $trustedLandlord = new TrustedLandlord();
        $trustedLandlord->setStatus(TrustedLandlordStatus::TRUSTED);
        $trustedLandlord->setCheckMailingAddress($checkMailingAddress);

        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $group->setTrustedLandlord($trustedLandlord);

        $propertyAddress = new PropertyAddress();
        $propertyAddress->setIndex('999AndanteRdSantaBarbaraCA');

        $property = new Property();
        $property->setPropertyAddress($propertyAddress);

        $contract = new Contract();
        $contract->setGroup($group);
        $contract->setProperty($property);
        $payment = new Payment();
        $payment->setContract($contract);

        $paymentRule = new PaymentMatchCheckMailingAddressAndUnitAddressDTR();
        $this->assertTrue(
            $paymentRule->checkPayment($payment),
            'Should return True if Payment Has Different CheckMailingAddress And unit\'s address'
        );
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfPaymentHasEqualsMailingAddressAndUnitAddress()
    {
        $checkMailingAddress = new CheckMailingAddress();
        $checkMailingAddress->setIndex('999AndanteRdSantaBarbaraCA');

        $trustedLandlord = new TrustedLandlord();
        $trustedLandlord->setStatus(TrustedLandlordStatus::TRUSTED);
        $trustedLandlord->setCheckMailingAddress($checkMailingAddress);

        $group = new Group();
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $group->setTrustedLandlord($trustedLandlord);

        $propertyAddress = new PropertyAddress();
        $propertyAddress->setIndex('999AndanteRdSantaBarbaraCA');

        $property = new Property();
        $property->setPropertyAddress($propertyAddress);

        $contract = new Contract();
        $contract->setGroup($group);
        $contract->setProperty($property);
        $payment = new Payment();
        $payment->setContract($contract);

        $paymentRule = new PaymentMatchCheckMailingAddressAndUnitAddressDTR();
        $this->assertFalse(
            $paymentRule->checkPayment($payment),
            'Should return False if payment has equals CheckMailingAddress and unit\'s address'
        );
    }

    /**
     * @test
     */
    public function shouldReturnReasonCode()
    {
        $paymentRule = new PaymentMatchCheckMailingAddressAndUnitAddressDTR();

        $this->assertEquals(
            PaymentFlaggedReason::DTR_PAYMENT_MATCH_ADDRESSES,
            $paymentRule->getReasonCode(),
            sprintf(
                'Reason code is invalid should be "%s" instead of "%s"',
                PaymentFlaggedReason::DTR_PAYMENT_MATCH_ADDRESSES,
                $paymentRule->getReasonCode()
            )
        );
    }
}
