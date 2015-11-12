<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay;

use ACI\Client\CollectPay\Enum\BankAccountType;
use CreditJeeves\DataBundle\Entity\Order;
use Payum\AciCollectPay\Model\Enum\FundingAccountType;
use Payum\AciCollectPay\Model\Payment;
use Payum\AciCollectPay\Request\CaptureRequest\Capture;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentAccountInterface;
use RentJeeves\DataBundle\Entity\GroupAwareInterface;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Entity\UserAwareInterface;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\DataBundle\Enum\BankAccountType as BankAccountTypeEnum;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

class PaymentManager extends AbstractManager
{
    /**
     * @param  Order $order
     * @param  PaymentAccountInterface $paymentAccount
     * @param  string $paymentType
     * @return bool
     * @throws \Exception
     */
    public function executePayment(Order $order, PaymentAccountInterface $paymentAccount, $paymentType)
    {
        $payment = new Payment();

        $this->mapPaymentDetails($payment, $paymentAccount, $order, $paymentType);

        $payment->setFundingAccountId($paymentAccount->getToken());
        $payment->setTransactionCode($order->getId());
        $payment->setAmount($order->getSum());
        $payment->setFee($order->getFee());

        if ($paymentAccount->getType() == PaymentAccountType::BANK) {
            $payment->setFundingAccountType(FundingAccountType::BANK);

            switch ($paymentAccount->getBankAccountType()) {
                case BankAccountTypeEnum::CHECKING:
                    $payment->setAchType(BankAccountType::PERSONAL_CHECKING);
                    break;
                case BankAccountTypeEnum::SAVINGS:
                    $payment->setAchType(BankAccountType::PERSONAL_SAVINGS);
                    break;
                default:
                    $payment->setAchType(BankAccountType::BUSINESS_CHECKING);
                    break;
            }
        } else {
            $payment->setDescriptor($order->getDescriptor());
            $payment->setFundingAccountType(FundingAccountType::CCARD);
        }

        $request = new Capture($payment);

        $transaction = new Transaction();

        $transaction->setOrder($order);
        $transaction->setMerchantName($payment->getDivisionBusinessId());
        $transaction->setBatchId($this->getBatchIdForOrder($order));
        $transaction->setBatchDate(new \DateTime());
        $transaction->setAmount($order->getSum() + $order->getFee());

        try {
            $this->paymentProcessor->execute($request);
        } catch (\Exception $e) {
            $this->logger->alert(
                sprintf('[ACI CollectPay Payment Exception]:Order(%s):%s', $order->getId(), $e->getMessage())
            );
            $transaction->setMessages($e->getMessage());
            $transaction->setIsSuccessful(false);
            $this->em->persist($transaction);
            $this->em->flush();
            throw $e;
        }

        if (!$request->getIsSuccessful()) {
            $this->logger->alert(
                sprintf('[ACI CollectPay Payment Error]:Order(%s):%s', $order->getId(), $request->getMessages())
            );
        }

        $transaction->setMessages(self::removeDebugInformation($request->getMessages()));
        $transaction->setIsSuccessful($request->getIsSuccessful());
        $transaction->setTransactionId($request->getModel()->getConfirmationNumber());

        $order->addTransaction($transaction);

        $this->em->persist($transaction);

        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Created new %s %s transaction with order id = "%d"',
                $request->getIsSuccessful() ? "successful" : "failed",
                $paymentType,
                $order->getId()
            )
        );

        return !!$request->getIsSuccessful();
    }

    /**
     * @param Payment $payment
     * @param PaymentAccountInterface $paymentAccount
     * @param Order $order
     * @param $paymentType
     */
    protected function mapPaymentDetails(
        Payment $payment,
        PaymentAccountInterface $paymentAccount,
        Order $order,
        $paymentType
    ) {
        if ($paymentAccount instanceof GroupAwareInterface && $paymentType === PaymentGroundType::CHARGE) {
            $groupProfile = $paymentAccount->getGroup()->getAciCollectPayProfile();
            $payment->setProfileId($groupProfile->getProfileId());
            $payment->setDivisionBusinessId($this->virtualTerminalBusinessId);
            $payment->setBillingAccountNumber($groupProfile->getBillingAccountNumber());
        } elseif ($paymentAccount instanceof UserAwareInterface &&
            ($paymentType === PaymentGroundType::RENT || $paymentType === PaymentGroundType::REPORT)
        ) {
            $userProfile = $paymentAccount->getUser()->getAciCollectPayProfile();
            $divisionId = $order->getDepositAccount()->getMerchantName();
            $payment->setProfileId($userProfile->getProfileId());
            $payment->setDivisionBusinessId($divisionId);
            $payment->setBillingAccountNumber(
                $userProfile->getBillingAccountForDivisionId($divisionId)->getBillingAccountNumber()
            );
        } else {
            throw new PaymentProcessorInvalidArgumentException(
                'Undefined type of payment account or incorrect payment type'
            );
        }
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    protected function getBatchIdForOrder(Order $order)
    {
        $depositAccount = $order->getDepositAccount();
        $date = new \DateTime();

        return sprintf('%dB%s', $depositAccount->getId(), $date->format('Ymd'));
    }
}
