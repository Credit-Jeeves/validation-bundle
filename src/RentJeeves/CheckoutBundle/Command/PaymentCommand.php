<?php
namespace RentJeeves\CheckoutBundle\Command;

use Payum\Heartland\Bridge\Doctrine\Entity\PaymentDetails;
use Payum\Heartland\Soap\Base\BillTransaction;
use Payum\Heartland\Soap\Base\CardProcessingMethod;
use Payum\Heartland\Soap\Base\MakePaymentRequest;
use Payum\Heartland\Soap\Base\TokenToCharge;
use Payum\Heartland\Soap\Base\Transaction;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use RentJeeves\DataBundle\Enum\PaymentType;
use RentJeeves\CoreBundle\Traits\DateCommon;

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
        $date = new \DateTime();
        $days = $this->getDueDays();
        /** @var PaymentRepository $repo */
        $repo = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment');
        $payments = $repo->getActivePayments($days, $date->format('n'), $date->format('Y'));
        $output->write('Start payment process');
        /** @var \Payum\Payment $payum */
        $payum = $this->getContainer()->get('payum')->getPayment('heartland');

        foreach ($payments as $row) {
            /** @var Payment $payment */
            $payment = $row[0];
            $contract = $payment->getContract();
            $tenant = $contract->getTenant();


//            $request = new MakePaymentRequest();
//            $billTransaction = new BillTransaction();
////        $billTransaction->setBillType('Bill Payment');
//            $billTransaction->setID1(1);
//            $billTransaction->setAmountToApplyToBill($amount);
//            $request->getBillTransactions()->setBillTransaction(array($billTransaction));
//
//            $tokenToCharge = new TokenToCharge();
//            $tokenToCharge->setAmount($amount);
//            $tokenToCharge->setCardProcessingMethod(CardProcessingMethod::UNASSIGNED);
//            $tokenToCharge->setExpectedFeeAmount(static::$feeAmount);
//            $tokenToCharge->setToken(static::$token);
//
//
//            $request->getTokensToCharge()->setTokenToCharge(array($tokenToCharge));
//
//            $transaction = new Transaction();
//            $transaction->setAmount($amount);
//            $transaction->setFeeAmount(static::$feeAmount);
//            $request->setTransaction($transaction);
//
//
//            $paymentDetails = new PaymentDetails();
//            $paymentDetails->setMerchantName($GLOBALS['__PAYUM_HEARTLAND_MERCHANT_NAME']);
//            $paymentDetails->setRequest($request);
//
//            $captureRequest = new CaptureRequest($paymentDetails);
//            $payment->execute($captureRequest);
//
//            $statusRequest = new BinaryMaskStatusRequest($captureRequest->getModel());
//            $payment->execute($statusRequest);
//
//            /** @var GetTokenResponse $response */
//            $response = $statusRequest->getModel()->getResponse();
//
//            $statusRequest->isSuccess();


            $output->write('.');
        }
        $output->writeln('OK');
    }
}
