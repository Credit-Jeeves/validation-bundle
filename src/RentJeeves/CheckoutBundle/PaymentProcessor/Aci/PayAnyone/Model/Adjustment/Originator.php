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
    protected $clearedChecks;

    /**
     * @Serializer\Type("RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Transaction")
     * @Serializer\SerializedName("REFUNDED_OUTDATED_CHECKS")
     */
    protected $refundedOutdatedChecks;

    /**
     * @Serializer\Type("RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Transaction")
     * @Serializer\SerializedName("REFUNDED_DUPLICATE_PAYMENTS")
     */
    protected $refundedDuplicatePayments;

    /**
     * @Serializer\Type("RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Transaction")
     * @Serializer\SerializedName("REFUNDED_CANCELLED_PAYMENTS")
     */
    protected $refundedCancelledPayments;

    /**
     * @Serializer\Type("RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Transaction")
     * @Serializer\SerializedName("REFUNDED_RETURNED_PAYMENTS")
     */
    protected $refundedReturnedPayments;

    /**
     * @Serializer\Type("RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Transaction")
     * @Serializer\SerializedName("CORRECTED_DUPLICATE_PAYMENTS")
     */
    protected $correctedDuplicatePayments;

    /**
     * @Serializer\Type("RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Transaction")
     * @Serializer\SerializedName("CORRECTED_RETURNED_PAYMENTS")
     */
    protected $correctedReturnedPayments;

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
     * @Serializer\Type("RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Transaction")
     * @Serializer\SerializedName("RETURNED_PAYMENTS")
     */
    protected $returnedPayments;

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

    /**
     * @return mixed
     */
    public function getClearedChecks()
    {
        return $this->clearedChecks;
    }

    /**
     * @param mixed $clearedChecks
     */
    public function setClearedChecks($clearedChecks)
    {
        $this->clearedChecks = $clearedChecks;
    }

    /**
     * @return mixed
     */
    public function getRefundedDuplicatePayments()
    {
        return $this->refundedDuplicatePayments;
    }

    /**
     * @param mixed $refundedDuplicatePayments
     */
    public function setRefundedDuplicatePayments($refundedDuplicatePayments)
    {
        $this->refundedDuplicatePayments = $refundedDuplicatePayments;
    }

    /**
     * @return mixed
     */
    public function getRefundedCancelledPayments()
    {
        return $this->refundedCancelledPayments;
    }

    /**
     * @param mixed $refundedCancelledPayments
     */
    public function setRefundedCancelledPayments($refundedCancelledPayments)
    {
        $this->refundedCancelledPayments = $refundedCancelledPayments;
    }

    /**
     * @return mixed
     */
    public function getRefundedReturnedPayments()
    {
        return $this->refundedReturnedPayments;
    }

    /**
     * @param mixed $refundedReturnedPayments
     */
    public function setRefundedReturnedPayments($refundedReturnedPayments)
    {
        $this->refundedReturnedPayments = $refundedReturnedPayments;
    }

    /**
     * @return mixed
     */
    public function getCorrectedDuplicatePayments()
    {
        return $this->correctedDuplicatePayments;
    }

    /**
     * @param mixed $correctedDuplicatePayments
     */
    public function setCorrectedDuplicatePayments($correctedDuplicatePayments)
    {
        $this->correctedDuplicatePayments = $correctedDuplicatePayments;
    }

    /**
     * @return mixed
     */
    public function getCorrectedReturnedPayments()
    {
        return $this->correctedReturnedPayments;
    }

    /**
     * @param mixed $correctedReturnedPayments
     */
    public function setCorrectedReturnedPayments($correctedReturnedPayments)
    {
        $this->correctedReturnedPayments = $correctedReturnedPayments;
    }

    /**
     * @return mixed
     */
    public function getReturnedPayments()
    {
        return $this->returnedPayments;
    }

    /**
     * @param mixed $returnedPayments
     */
    public function setReturnedPayments($returnedPayments)
    {
        $this->returnedPayments = $returnedPayments;
    }

    /**
     * @return mixed
     */
    public function getDepositTransactions()
    {
        return $this->clearedChecks;
    }

    /**
     * @return array all reversal transactions
     */
    public function getReversalTransactions()
    {
        return [
            'REFUNDED_DUPLICATE_PAYMENTS' => $this->getRefundedDuplicatePayments(),
            'REFUNDED_CANCELLED_PAYMENTS' => $this->getRefundedCancelledPayments(),
            'REFUNDED_OUTDATED_CHECKS' => $this->getRefundedOutdatedChecks(),
            'REFUNDED_RETURNED_PAYMENTS' => $this->getRefundedReturnedPayments(),
            'REFUNDED_STOPPED_CHECKS' => $this->getRefundedStoppedChecks(),
            'CORRECTED_DUPLICATE_PAYMENTS' => $this->getCorrectedDuplicatePayments(),
            'CORRECTED_RETURNED_PAYMENTS' => $this->getCorrectedReturnedPayments(),
            'REISSUED_STOPPED_CHECKS' => $this->getReissuedStoppedChecks(),
            'RETURNED_PAYMENTS' => $this->getReturnedPayments(),
            'STOPPED_CHECKS' => $this->getStoppedChecks(),
        ];
    }
}
