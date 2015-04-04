<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Group;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorInterface as PaymentProcessor;
use RentJeeves\DataBundle\Enum\PaymentProcessor as PaymentProcessorEnum;

/**
 * @DI\Service("payment_processor.factory")
 */
class PaymentProcessorFactory
{
    /**
     * @var PaymentProcessorHeartland
     */
    protected $heartland;

    /**
     * @var PaymentProcessorAciCollectPay
     */
    protected $aciCollectPay;

    /**
     * @DI\InjectParams({
     *     "heartland" = @DI\Inject("payment_processor.heartland"),
     *     "aciCollectPay" = @DI\Inject("payment_processor.aci_collect_pay")
     * })
     */
    public function setPaymentProcessors(PaymentProcessor $heartland, PaymentProcessor $aciCollectPay)
    {
        $this->heartland = $heartland;
        $this->aciCollectPay = $aciCollectPay;
    }

    /**
     * Returns a payment processor for a given payment.
     *
     * @param Group $group
     * @return PaymentProcessor
     * @throws PaymentProcessorInvalidArgumentException
     */
    public function getPaymentProcessor(Group $group)
    {
        switch ($group->getGroupSettings()->getPaymentProcessor()) {
            case PaymentProcessorEnum::ACI_COLLECT_PAY:
                return $this->aciCollectPay;
            case PaymentProcessorEnum::HEARTLAND:
                return $this->heartland;
            default:
                throw new PaymentProcessorInvalidArgumentException(
                    sprintf(
                        'Unknown processor type for group "%s" with id "%d"',
                        $group->getName(),
                        $group->getId()
                    )
                );
        }
    }
}
