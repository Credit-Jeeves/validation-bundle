<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor\Heartland;

use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use Payum2\Heartland\Model\PaymentDetails;
use Payum2\Heartland\Soap\Base\BillTransaction;
use Payum2\Heartland\Soap\Base\CardProcessingMethod;
use Payum2\Heartland\Soap\Base\MakePaymentRequest;
use Payum2\Heartland\Soap\Base\TokenToCharge;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

class PayHeartland extends BasePayHeartland
{
    /**
     * {@inheritdoc}
     */
    protected function getPaymentDetails(OrderSubmerchant $order, $paymentType)
    {
        $request = new MakePaymentRequest();

        $billTransaction = new BillTransaction();
        $billTransaction->setAmountToApplyToBill($order->getSum());
        $request->getBillTransactions()->setBillTransaction([$billTransaction]);

        $request->getTransaction()
            ->setAmount($order->getSum())
            ->setFeeAmount($order->getFee());

        $paymentDetails = new PaymentDetails();
        $paymentDetails->setRequest($request);
        $paymentDetails->setAmount($order->getSum() + $order->getFee());

        if (PaymentGroundType::RENT == $paymentType) {
            $contract = $order->getContract();
            $paymentDetails->setMerchantName($contract->getGroup()->getMerchantName());

            $billTransaction->setID1(str_replace(',', '', $contract->getProperty()->getShrinkAddress()));
            if ($contract->getUnit()) { // For houses, there are no units
                $billTransaction->setID2($contract->getUnit()->getName());
            }

            $tenant = $contract->getTenant();
            $billTransaction->setID3(sprintf('%s %s', $tenant->getFirstName(), $tenant->getLastName()));
            $billTransaction->setID4($contract->getGroup()->getID4StatementDescriptor());
            $order->setDescriptor($contract->getGroup()->getID4StatementDescriptor());
        } elseif (PaymentGroundType::REPORT == $paymentType) {
            $paymentDetails->setMerchantName($this->rtMerchantName);
            $billTransaction->setID1('report');
        }

        return $paymentDetails;
    }

    /**
     * {@inheritdoc}
     */
    protected function addToken(PaymentDetails $paymentDetails, $token, OrderSubmerchant $order)
    {
        $tokenToCharge = new TokenToCharge();
        $tokenToCharge->setAmount($order->getSum());
        $tokenToCharge->setExpectedFeeAmount($order->getFee());
        $tokenToCharge->setCardProcessingMethod(CardProcessingMethod::UNASSIGNED);
        $tokenToCharge->setToken($token);

        $tokensToCharge = $paymentDetails->getRequest()->getTokensToCharge()->getTokenToCharge();
        if (!is_array($tokensToCharge)) {
            $tokensToCharge = [];
        }
        $tokensToCharge[] = $tokenToCharge;
        $paymentDetails->getRequest()->getTokensToCharge()->setTokenToCharge($tokensToCharge);
    }
}
