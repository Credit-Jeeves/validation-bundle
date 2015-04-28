<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor\Heartland;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Payum2\Bundle\PayumBundle\Registry\ContainerAwareRegistry;
use Payum2\Heartland\Model\PaymentDetails;
use Payum2\Heartland\Soap\Base\BillTransaction;
use Payum2\Heartland\Soap\Base\CardProcessingMethod;
use Payum2\Heartland\Soap\Base\MakePaymentRequest;
use Payum2\Heartland\Soap\Base\TokenToCharge;
use Payum2\Payment as Payum;
use Payum2\Request\BinaryMaskStatusRequest;
use Payum2\Request\CaptureRequest;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

/**
 * @DI\Service("payment.pay_heartland")
 */
class PayHeartland
{
    /**
     * @var Payum
     */
    protected $payum;
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var PaymentDetailsMapper
     */
    protected $paymentDetailsMapper;
    /**
     * @var string
     */
    protected $rtMerchantName;

    /**
     * @param ContainerAwareRegistry $payum
     * @param EntityManager          $em
     * @param PaymentDetailsMapper   $paymentDetailsMapper
     * @param string                 $rtMerchantName
     *
     * @DI\InjectParams({
     *     "payum" = @DI\Inject("payum2"),
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager"),
     *     "paymentDetailsMapper" = @DI\Inject("payment.heartland.payment_details_mapper"),
     *     "rtMerchantName" = @DI\Inject("%rt_merchant_name%"),
     * })
     */
    public function __construct(
        ContainerAwareRegistry $payum,
        EntityManager $em,
        PaymentDetailsMapper $paymentDetailsMapper,
        $rtMerchantName
    ) {
        $this->payum = $payum->getPayment('heartland');
        $this->em = $em;
        $this->paymentDetailsMapper = $paymentDetailsMapper;
        $this->rtMerchantName = $rtMerchantName;
    }

    /**
     * Executes a payment taking money from given payment account.
     *
     * @param  Order          $order
     * @param  PaymentAccount $paymentAccount
     * @param  string         $paymentType
     * @return string
     */
    public function executePayment(Order $order, PaymentAccount $paymentAccount, $paymentType = PaymentGroundType::RENT)
    {
        $paymentDetails = $this->getPaymentDetails($order, $paymentType);

        /** @var MakePaymentRequest $request */
        $request = $paymentDetails->getRequest();

        /** @var BillTransaction $billTransaction */
        $billTransaction = $request->getBillTransactions()->getBillTransaction()[0];

        if (PaymentGroundType::RENT == $paymentType) {
            $contract = $order->getContract();
            $billTransaction->setID1(str_replace(",", "", $contract->getProperty()->getShrinkAddress()));
            if ($contract->getUnit()) { // For houses, there are no units
                $billTransaction->setID2($contract->getUnit()->getName());
            }
            $tenant = $contract->getTenant();
            $billTransaction->setID3(sprintf("%s %s", $tenant->getFirstName(), $tenant->getLastName()));
            $billTransaction->setID4($contract->getGroup()->getID4StatementDescriptor());
        }
        if (PaymentGroundType::REPORT == $paymentType) {
            $billTransaction->setID1("report");
        }

        /** @var Transaction $transaction */
        $transaction = $this->paymentDetailsMapper->map($paymentDetails);
        $transaction->setOrder($order);
        $transaction->setPaymentAccount($paymentAccount);

        $this->addToken($paymentDetails, $paymentAccount->getToken(), $order);

        $statusRequest = $this->execute($paymentDetails);

        $transaction = $this->paymentDetailsMapper->map($paymentDetails, $transaction);
        $isSuccessful = $statusRequest->isSuccess();
        $transaction->setIsSuccessful($isSuccessful);
        $order->addTransaction($transaction);

        $this->em->persist($transaction);

        return $this->getOrderStatus($order, $isSuccessful);
    }

    /**
     * @param  Order          $order
     * @param  string         $paymentType
     * @return PaymentDetails
     */
    protected function getPaymentDetails(Order $order, $paymentType)
    {
        $request = new MakePaymentRequest();

        $billTransaction = new BillTransaction();
        $billTransaction->setAmountToApplyToBill($order->getSum());
        $request->getBillTransactions()->setBillTransaction(array($billTransaction));

        $request->getTransaction()
            ->setAmount($order->getSum())
            ->setFeeAmount($order->getFee());

        $paymentDetails = new PaymentDetails();
        $paymentDetails->setRequest($request);
        $paymentDetails->setAmount($order->getSum() + $order->getFee());

        if (PaymentGroundType::RENT == $paymentType) {
            $paymentDetails->setMerchantName($order->getContract()->getGroup()->getMerchantName());
        }
        if (PaymentGroundType::REPORT == $paymentType) {
            $paymentDetails->setMerchantName($this->rtMerchantName);
        }

        return $paymentDetails;
    }

    /**
     * @param  PaymentDetails $paymentDetails
     * @param  string         $token
     * @param  Order          $order
     * @return $this
     */
    protected function addToken(PaymentDetails $paymentDetails, $token, Order $order)
    {
        $tokenToCharge = new TokenToCharge();
        $tokenToCharge->setAmount($order->getSum());
        $tokenToCharge->setExpectedFeeAmount($order->getFee());
        $tokenToCharge->setCardProcessingMethod(CardProcessingMethod::UNASSIGNED);
        $tokenToCharge->setToken($token);

        $tokensToCharge = $paymentDetails->getRequest()->getTokensToCharge()->getTokenToCharge();
        if (!is_array($tokensToCharge)) {
            $tokensToCharge = array();
        }
        $tokensToCharge[] = $tokenToCharge;
        $paymentDetails->getRequest()->getTokensToCharge()->setTokenToCharge($tokensToCharge);

        return $this;
    }

    /**
     * @param  PaymentDetails          $paymentDetails
     * @return BinaryMaskStatusRequest
     */
    protected function execute(PaymentDetails $paymentDetails)
    {
        $captureRequest = new CaptureRequest($paymentDetails);
        $this->payum->execute($captureRequest);

        /** @var PaymentDetails $model */
        $model = $captureRequest->getModel();
        $statusRequest = new BinaryMaskStatusRequest($model);
        $this->payum->execute($statusRequest);

        return $statusRequest;
    }

    /**
     * Defines orders status.
     * For credit card payments order already becomes COMPLETE, for ACH payments - PENDING.
     *
     * @param  Order  $order
     * @param  bool   $isSuccessful
     * @return string
     */
    protected function getOrderStatus(Order $order, $isSuccessful)
    {
        if (!$isSuccessful) {
            return OrderStatus::ERROR;
        }
        if (OrderType::HEARTLAND_CARD == $order->getType()) {
            return OrderStatus::COMPLETE;
        }

        return OrderStatus::PENDING;
    }
}
