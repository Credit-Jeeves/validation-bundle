<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("ORIGINATOR")
 */
class Originator
{
    /**
     * @Serializer\Type("RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Transaction")
     * @Serializer\SerializedName("CLEARED_CHECKS")
     */
    protected $depositTransactions;

    /**
     * @Serializer\Type("RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Transaction")
     * @Serializer\SerializedName("REFUNDED_OUTDATED_CHECKS")
     */
    protected $refundedOutdatedChecks;

    /**
     * @Serializer\Type("RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Transaction")
     * @Serializer\SerializedName("REFUNDED_STOPPED_CHECKS")
     */
    protected $refundedStoppedChecks;

    /**
     * @Serializer\Type("RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Transaction")
     * @Serializer\SerializedName("REISSUED_STOPPED_CHECKS")
     */
    protected $reissuedStoppedChecks;

    /**
     * @Serializer\Type("RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Transaction")
     * @Serializer\SerializedName("STOPPED_CHECKS")
     */
    protected $stoppedChecks;

    /**
     * @return mixed
     */
    public function getDepositTransactions()
    {
        return $this->depositTransactions;
    }

    /**
     * @param mixed $depositTransactions
     */
    public function setDepositTransactions($depositTransactions)
    {
        $this->depositTransactions = $depositTransactions;
    }

    /**
     * @return mixed
     */
    public function getRefundedOutdatedChecks()
    {
        return $this->refundedOutdatedChecks;
    }

    /**
     * @param mixed $refundedOutdatedChecks
     */
    public function setRefundedOutdatedChecks($refundedOutdatedChecks)
    {
        $this->refundedOutdatedChecks = $refundedOutdatedChecks;
    }

    /**
     * @return mixed
     */
    public function getRefundedStoppedChecks()
    {
        return $this->refundedStoppedChecks;
    }

    /**
     * @param mixed $refundedStoppedChecks
     */
    public function setRefundedStoppedChecks($refundedStoppedChecks)
    {
        $this->refundedStoppedChecks = $refundedStoppedChecks;
    }

    /**
     * @return mixed
     */
    public function getReissuedStoppedChecks()
    {
        return $this->reissuedStoppedChecks;
    }

    /**
     * @param mixed $reissuedStoppedChecks
     */
    public function setReissuedStoppedChecks($reissuedStoppedChecks)
    {
        $this->reissuedStoppedChecks = $reissuedStoppedChecks;
    }

    /**
     * @return mixed
     */
    public function getStoppedChecks()
    {
        return $this->stoppedChecks;
    }

    /**
     * @param mixed $stoppedChecks
     */
    public function setStoppedChecks($stoppedChecks)
    {
        $this->stoppedChecks = $stoppedChecks;
    }
}
