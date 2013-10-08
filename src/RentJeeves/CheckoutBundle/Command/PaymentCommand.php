<?php
namespace RentJeeves\CheckoutBundle\Command;

use CreditJeeves\DataBundle\Entity\Operation;
//use CreditJeeves\DataBundle\Entity\OrderOperation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Heartland as PaymentDetails;
use Payum\Heartland\Soap\Base\BillTransaction;
use Payum\Heartland\Soap\Base\CardProcessingMethod;
use Payum\Heartland\Soap\Base\GetTokenResponse;
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
use RentJeeves\DataBundle\Enum\PaymentType;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\CoreBundle\Traits\DateCommon;
use Payum\Payment as Payum;
use \DateTime;

class PaymentCommand extends ContainerAwareCommand
{
    use DateCommon;

    protected function configure()
    {
        $this
            ->setName('Payment:process')
            ->setDescription('Start auto payments');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new DateTime();
        $days = $this->getDueDays();
        /** @var PaymentRepository $repo */
        $repo = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment');
        $payments = $repo->getActivePayments($days, $date->format('n'), $date->format('Y'));
        $output->write('Start payment process');
        /** @var Payum $payum */
        $payum = $this->getContainer()->get('payum')->getPayment('heartland');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        foreach ($payments as $row) {
            /** @var Payment $payment */
            $payment = $row[0];
            $paymentAccount = $payment->getPaymentAccount();
            $contract = $payment->getContract();
//            $tenant = $contract->getTenant();

            $amount = $payment->getAmount();
            $fee = 0;

            $order = new Order();
            $operation = new Operation();
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
            $order->setAmount($amount); // TODO findout about fee
            $order->setStatus(OrderStatus::NEWONE);
//            $order->setDaysLate(0); //FIXME Alex please put her correct value!


            $request = new MakePaymentRequest();
            $billTransaction = new BillTransaction();
            $billTransaction->setID1(1);
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

            $em->persist($order);
            $em->persist($operation);
            $em->flush();

            $captureRequest = new CaptureRequest($paymentDetails);
            $payum->execute($captureRequest);

            $statusRequest = new BinaryMaskStatusRequest($captureRequest->getModel());
            $payum->execute($statusRequest);

            if ($statusRequest->isSuccess()) {
                $order->setStatus(OrderStatus::COMPLETE);
                $output->write('.');
            } else {
                $order->setStatus(OrderStatus::ERROR);
                $output->writeln("\n" . $paymentDetails->getMessages());
            }
            $paymentDetails->setAmount($amount + $fee);
            $paymentDetails->setIsSuccessful($statusRequest->isSuccess());
            $em->persist($paymentDetails);
            $em->persist($order);
            $em->flush();
        }
        $output->writeln('OK');
    }
}
