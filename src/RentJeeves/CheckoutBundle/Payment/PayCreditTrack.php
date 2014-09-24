<?php
namespace RentJeeves\CheckoutBundle\Payment;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\ReportD2c;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use Payum\Heartland\Soap\Base\BillTransaction;
use Payum\Heartland\Soap\Base\MakePaymentRequest;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\JobRelatedReport;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Enum\PaymentAccountType;

/**
 * @DI\Service("payment.pay_credit_track")
 */
class PayCreditTrack extends Pay
{
    /**
     * @var double
     */
    protected $amount;

    /**
     * @var string
     */
    protected $rjGroupCode;

    /**
     * @DI\InjectParams({"rjGroupCode" = @DI\Inject("%rt_merchant_name%")})
     *
     * @param string $rjGroupCode
     *
     * @return $this
     */
    public function setRjGroupCode($rjGroupCode)
    {
        $this->rjGroupCode = $rjGroupCode;
        return $this;
    }

    /**
     * @DI\InjectParams({"amount" = @DI\Inject("%credittrack_payment_per_month%")})
     *
     * @param string $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = (double)$amount;
        return $this;
    }

    public function executePaymentAccount(PaymentAccount $paymentAccount)
    {
        $order = $this->getOrder();
        $order->setUser($paymentAccount->getUser());
        $order->setSum($this->amount);
        $order->setStatus(OrderStatus::NEWONE);

        /** @var DepositAccount $depositAccount */
        $depositAccount = $this->em
            ->getRepository('DataBundle:Group')
            ->findOneByCode($this->rjGroupCode)
            ->getDepositAccount();

        if (PaymentAccountType::CARD == $paymentAccount->getType()) {
            $order->setFee(round($order->getSum() * ($depositAccount->getFeeCC() / 100), 2));
            $order->setType(OrderType::HEARTLAND_CARD);
        } elseif (PaymentAccountType::BANK == $paymentAccount->getType()) {
            $order->setFee($depositAccount->getFeeACH());
            $order->setType(OrderType::HEARTLAND_BANK);
        }

        $paymentDetails = $this->getPaymentDetails();
        $paymentDetails->setMerchantName($depositAccount->getMerchantName());

        $this->em->persist($order);
        $this->em->flush();

        $this->addToken($paymentAccount->getToken());

        /** @var MakePaymentRequest $request */
        $request = $paymentDetails->getRequest();

        /** @var BillTransaction $billTransaction */
        $billTransaction = $request->getBillTransactions()->getBillTransaction()[0];

        $billTransaction->setID1("report");

        $statusRequest = $this->execute();

        if ($statusRequest->isSuccess()) {
            $order->setStatus(OrderStatus::COMPLETE);
            $report = new ReportD2c();
            $report->setUser($paymentAccount->getUser());
            $report->setRawData('');
            $operation = new Operation();
            $operation->setReportD2c($report);
            $operation->setPaidFor(new DateTime());
            $operation->setAmount($this->amount);
            $operation->setType(OperationType::REPORT);
            $order->addOperation($operation);
            $this->em->persist($operation);
            $this->em->persist($report);
            $job = new Job('experian-credit_profile:get', array('--app=rj'));
            $job->addRelatedEntity($report);
            $execute = new DateTime();
            $execute->modify("+5 minutes");
            $job->setExecuteAfter($execute);
            $this->em->persist($job);
        } else {
            $order->setStatus(OrderStatus::ERROR);
        }


        $paymentDetails->setIsSuccessful($statusRequest->isSuccess());
        $this->em->persist($paymentDetails);
        $this->em->persist($order);
        $this->em->flush();
        return $statusRequest;
    }
}
