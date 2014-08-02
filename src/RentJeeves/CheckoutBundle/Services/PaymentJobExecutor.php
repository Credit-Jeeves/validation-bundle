<?php
namespace RentJeeves\CheckoutBundle\Services;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityManager;
use Payum\Request\BinaryMaskStatusRequest;
use RentJeeves\CheckoutBundle\Payment\PayCreditTrack;
use RentJeeves\CheckoutBundle\Payment\PayRent;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\JobRelatedCreditTrack;
use RentJeeves\DataBundle\Entity\JobRelatedPayment;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("checkout.payment_job_executor")
 */
class PaymentJobExecutor
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var PayRent
     */
    protected $payRent;

    /**
     * @var PayCreditTrack
     */
    protected $payCreditTrack;

    /**
     * @var Job
     */
    protected $job;

    /**
     * @var int
     */
    protected $exitCode = 0;

    /**
     * @var string
     */
    protected $message = 'OK';

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager"),
     *     "payRent" = @DI\Inject("payment.pay_rent"),
     *     "payCreditTrack" = @DI\Inject("payment.pay_credit_track")
     * })
     */
    public function __construct($em, $payRent, $payCreditTrack)
    {
        $this->em = $em;
        $this->payRent = $payRent;
        $this->payCreditTrack = $payCreditTrack;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getExitCode()
    {
        return $this->exitCode;
    }

    /**
     * @param Job $job
     *
     * @return bool
     */
    public function execute(Job $job)
    {
        $this->job = $job;
        foreach ($this->job->getRelatedEntities() as $relatedEntity) {
            switch (true) {
                case $relatedEntity instanceof JobRelatedPayment:
                    return $this->executePayment($relatedEntity->getPayment());
                    break;
                case $relatedEntity instanceof JobRelatedCreditTrack:
                    return $this->executeCreditTrack($relatedEntity->getCreditTrackPaymentAccount());
                    break;
            }

        }
        $this->message = sprintf("Job ID:'%s' must have related payment", $job->getId());
        return false;
    }

    /**
     * @param BinaryMaskStatusRequest $statusRequest
     *
     * @return bool
     */
    protected function processStatus($statusRequest)
    {
        if (!$statusRequest->isSuccess()) {
            $this->message = $statusRequest->getModel()->getMessages();
            $this->exitCode = 1;
            return false;
        }
        return true;
    }

    /**
     * @param Payment $payment
     *
     * @return bool
     */
    protected function executePayment(Payment $payment)
    {
        $date = new DateTime();
        $contract = $payment->getContract();

        $filterClosure = function (Operation $operation) use ($date) {
            if (($order = $operation->getOrder()) &&
                $order->getCreatedAt()->format('Y-m-d') == $date->format('Y-m-d') &&
                OrderStatus::ERROR != $order->getStatus()
            ) {
                return true;
            }
            return false;
        };
        if ($contract->getOperations()->filter($filterClosure)->count()) {
            $this->message = 'Payment already executed.';
            $this->exitCode = 1;
            return false;
        }

        $this->payRent->newOrder();
        $this->job->addRelatedEntity($this->payRent->getOrder());
        $this->em->persist($this->job);
        return $this->processStatus($this->payRent->executePayment($payment));
    }

    /**
     * @param PaymentAccount $paymentAccount
     *
     * @return bool
     */
    protected function executeCreditTrack(PaymentAccount $paymentAccount)
    {
        $this->job->addRelatedEntity($this->payCreditTrack->getOrder());
        $this->em->persist($this->job);
        return $this->processStatus($this->payCreditTrack->executePaymentAccount($paymentAccount));
    }
}
