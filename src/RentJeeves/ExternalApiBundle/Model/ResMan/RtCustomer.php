<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Enum\PaymentAccepted;

class RtCustomer
{
    /**
     * it's lease id of contract
     *
     * @Serializer\SerializedName("CustomerID")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $customerId;

    /**
     * @Serializer\SerializedName("Customers")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\Customers")
     * @Serializer\Groups({"ResMan"})
     */
    protected $customers;

    /**
     * @Serializer\SerializedName("RT_Unit")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\RtUnit")
     * @Serializer\Groups({"ResMan"})
     */
    protected $rtUnit;

    /**
     * @Serializer\SerializedName("RTServiceTransactions")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\RtServiceTransactions")
     * @Serializer\Groups({"ResMan"})
     */
    protected $rtServiceTransactions;

    /**
     * @Serializer\SerializedName("PaymentAccepted")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $paymentAccepted;

    /**
     * @return string
     */
    public function getPaymentAccepted()
    {
        return $this->paymentAccepted;
    }

    /**
     * @param string $paymentAccepted
     */
    public function setPaymentAccepted($paymentAccepted)
    {
        $this->paymentAccepted = $paymentAccepted;
    }

    /**
     * @return RtServiceTransactions
     */
    public function getRtServiceTransactions()
    {
        return $this->rtServiceTransactions;
    }

    /**
     * @param RtServiceTransactions $rtServiceTransactions
     */
    public function setRtServiceTransactions(RtServiceTransactions $rtServiceTransactions)
    {
        $this->rtServiceTransactions = $rtServiceTransactions;
    }

    /**
     * @return RtUnit
     */
    public function getRtUnit()
    {
        return $this->rtUnit;
    }

    /**
     * @param RtUnit $rtUnit
     */
    public function setRtUnit(RtUnit $rtUnit)
    {
        $this->rtUnit = $rtUnit;
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return Customers
     */
    public function getCustomers()
    {
        return $this->customers;
    }

    /**
     * @param Customers $customers
     */
    public function setCustomers(Customers $customers)
    {
        $this->customers = $customers;
    }

    /**
     * @return integer
     */
    public function getRentTrackPaymentAccepted()
    {
        $paymentAccepted = strtolower($this->getPaymentAccepted());
        /**
         * Possible Values are:
         * Yes - All forms of payment accepted
         * No - Online payments are not accepted
         * Certified Funds Only - Only payments guaranteed to be successful are allowed
         * (Credit Card, Debit Card, Money Order, etc)
         *
         * Currently we don't work with 3 point - it's will be seperated task
         */

        return 'yes' === $paymentAccepted ? PaymentAccepted::ANY : PaymentAccepted::DO_NOT_ACCEPT;
    }

    /**
     * @return float|int
     */
    public function getRentTrackBalance()
    {
        $balance = 0;

        if ($rtTransactions = $this->getRtServiceTransactions()) {
            /**
             * @var $transaction Transactions
             */
            foreach ($rtTransactions->getTransactions() as $transaction) {

                $charge = $transaction->getCharge();
                $payment = $transaction->getPayment();
                $credit = $transaction->getConcession();

                if (!empty($charge)) {
                    $balance += $charge->getDetail()->getAmount();
                    continue;
                }

                if (!empty($payment)) {
                    $balance -= $payment->getDetail()->getAmount();
                    continue;
                }

                if (!empty($credit)) {
                    $balance -= $credit->getDetail()->getAmount();
                }
            }
        }

        return $balance;
    }
}
