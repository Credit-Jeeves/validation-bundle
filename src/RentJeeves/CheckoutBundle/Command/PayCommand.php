<?php
namespace RentJeeves\CheckoutBundle\Command;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use JMS\JobQueueBundle\Entity\Job;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Heartland as PaymentDetails;
use Payum\Heartland\Soap\Base\BillTransaction;
use Payum\Heartland\Soap\Base\CardProcessingMethod;
use Payum\Heartland\Soap\Base\MakePaymentRequest;
use Payum\Heartland\Soap\Base\TokenToCharge;
use Payum\Heartland\Soap\Base\Transaction;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\CoreBundle\Traits\DateCommon;
use Payum\Payment as Payum;
use \DateTime;
use \RuntimeException;

class PayCommand extends ContainerAwareCommand
{
    use DateCommon;

    protected function configure()
    {
        $this
            ->setName('payment:pay')
            ->addOption('jms-job-id', null, InputOption::VALUE_REQUIRED, 'ID of job')
            ->setDescription('Start payment');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $jobId = $input->getOption('jms-job-id');

        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata('JMSJobQueueBundle:Job', 'j');
        /** @var Job $job */
        $job = $em->createNativeQuery(
                "SELECT j.* FROM jms_jobs j
                    INNER JOIN jms_job_related_entities r ON r.job_id = j.id
                    WHERE j.executeAfter < :now AND j.state = :state AND j.id = :id
                    ORDER BY j.id ASC",
                $rsm
            )
            ->setParameter('state', Job::STATE_RUNNING)
            ->setParameter('now', new DateTime())
            ->setParameter('id', $jobId)
            ->getOneOrNullResult();
        if (empty($job)) {
            throw new RuntimeException("Option can not fid --jms-job-id={$jobId}");
        }

        $date = new DateTime();
        $days = $this->getDueDays();
        $orders = $em->getRepository('DataBundle:Order')
            ->getLastOrdersArray(
                $days,
                $date->format('n'),
                $date->format('Y')
            );
        $output->write('Start ');
        /** @var Payum $payum */
        $payum = $this->getContainer()->get('payum')->getPayment('heartland');


        /** @var Payment $payment */
        $payment = $job->findRelatedEntity('RentJeeves\DataBundle\Entity\Payment');

        if (empty($payment)) {
            throw new RuntimeException("Job ID:'{$jobId}' must have related payment");
        }

        $paymentAccount = $payment->getPaymentAccount();
        $contract = $payment->getContract();
        if (isset($orders[$contract->getId()]) &&
            $orders[$contract->getId()]['status'] == OrderStatus::COMPLETE &&
            $orders[$contract->getId()]['updated'] == $date->format('Y-m-d')
        ) {
            $output->writeln('Payment already executed.');
            exit(3);
        }
        $operation = $contract->getOperation();
        if (empty($operation)) {
            $operation = new Operation();
        }
        $amount = $payment->getAmount();
        $fee = 0;

        $order = new Order();
        $operation->setType(OperationType::RENT);
        $operation->setContract($contract);

        if (PaymentAccountType::CARD == $paymentAccount->getType()) {
            $fee = round($amount * ((double)$this->getContainer()->getParameter('payment_card_fee') / 100), 2);
            $order->setType(OrderType::HEARTLAND_CARD);
        } elseif (PaymentAccountType::BANK == $paymentAccount->getType()) {
            $fee = (double)$this->getContainer()->getParameter('payment_bank_fee');
            $order->setType(OrderType::HEARTLAND_BANK);
        }

        $order->addOperation($operation);
        $order->setUser($paymentAccount->getUser());
        $order->setAmount($amount);
        $order->setStatus(OrderStatus::NEWONE);

        $request = new MakePaymentRequest();

        $billTransaction = new BillTransaction();
        $billTransaction->setID1(str_replace(",", "", $contract->getProperty()->getFullAddress()));
        if ($contract->getUnit()) { // For houses, there are no units
            $billTransaction->setID2($contract->getUnit()->getName());
        }
        $tenant = $contract->getTenant();
        $billTransaction->setID3(sprintf("%s %s", $tenant->getFirstName(), $tenant->getLastName()));
        $billTransaction->setID4($contract->getGroup()->getName());

        $billTransaction->setAmountToApplyToBill($amount);
        $request->getBillTransactions()->setBillTransaction(array($billTransaction));

        $tokenToCharge = new TokenToCharge();
        $tokenToCharge->setAmount($amount);
        $tokenToCharge->setExpectedFeeAmount($fee);
        $tokenToCharge->setCardProcessingMethod(CardProcessingMethod::UNASSIGNED);
        $tokenToCharge->setToken($paymentAccount->getToken());

        $request->getTokensToCharge()->setTokenToCharge(array($tokenToCharge));

        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setFeeAmount($fee);
        $request->setTransaction($transaction);

        $paymentDetails = new PaymentDetails();
        $paymentDetails->setMerchantName($contract->getGroup()->getMerchantName());
        $paymentDetails->setRequest($request);
        $paymentDetails->setOrder($order);

        if (PaymentTypeEnum::ONE_TIME == $payment->getType() ||
            date('n') == $payment->getEndMonth() && date('Y') == $payment->getEndYear()
        ) {
            $payment->setStatus(PaymentStatus::CLOSE);
            $em->persist($payment);
        }
        $em->persist($order);
        $em->persist($operation);
        $em->flush();

        $captureRequest = new CaptureRequest($paymentDetails);
        $payum->execute($captureRequest);

        $statusRequest = new BinaryMaskStatusRequest($captureRequest->getModel());
        $payum->execute($statusRequest);
        $order->addHeartland($paymentDetails);
        if ($statusRequest->isSuccess()) {
            $order->setStatus(OrderStatus::COMPLETE);
            $contract->shiftPaidTo($amount);
            $status = $contract->getStatus();
            if (in_array($status, array(ContractStatus::INVITE, ContractStatus::APPROVED))) {
                $contract->setStatus(ContractStatus::CURRENT);
            }
        } else {
            $order->setStatus(OrderStatus::ERROR);
            $output->write($statusRequest->getMessage());
        }
        $paymentDetails->setAmount($amount + $fee);
        $paymentDetails->setIsSuccessful($statusRequest->isSuccess());
        $em->persist($paymentDetails);
        $em->persist($order);
        $em->persist($contract);
        $em->flush();
        $em->clear();
        $output->writeln('OK');
    }
}
