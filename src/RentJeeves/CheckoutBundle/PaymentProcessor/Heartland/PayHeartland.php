<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor\Heartland;

use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry;
use Payum\Heartland\Soap\Base\BillTransaction;
use Payum\Heartland\Soap\Base\CardProcessingMethod;
use Payum\Heartland\Soap\Base\MakePaymentRequest;
use Payum\Heartland\Soap\Base\TokenToCharge;
use Payum\Payment as Payum;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentManagerInterface;
use RentJeeves\DataBundle\Entity\Heartland;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

/**
 * @DI\Service("payment.pay_heartland")
 */
class PayHeartland implements PaymentManagerInterface
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
     * @var string
     */
    protected $rtMerchantName;

    /**
     * @DI\InjectParams({
     *     "payum" = @DI\Inject("payum"),
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager"),
     *     "rtMerchantName" = @DI\Inject("%rt_merchant_name%"),
     * })
     */
    public function __construct(ContainerAwareRegistry $payum, $em, $rtMerchantName)
    {
        $this->payum = $payum->getPayment('heartland');
        $this->em = $em;
        $this->rtMerchantName = $rtMerchantName;
    }

    /**
     * Executes a payment taking money from given payment account.
     *
     * @param Order $order
     * @param PaymentAccount $paymentAccount
     * @param string $paymentType
     * @return bool
     */
    public function executePayment(Order $order, PaymentAccount $paymentAccount, $paymentType = PaymentGroundType::RENT)
    {
        $paymentDetails = $this->getPaymentDetails($order, $paymentType);
        $paymentDetails->setPaymentAccount($paymentAccount);

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

        $this->addToken($paymentDetails, $paymentAccount->getToken());

        $statusRequest = $this->execute($paymentDetails, $order);
        $isSuccessful = $statusRequest->isSuccess();
        $paymentDetails->setIsSuccessful($isSuccessful);
        $this->em->persist($paymentDetails);

        return $isSuccessful;
    }

    /**
     * @return Heartland
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

        $paymentDetails = new Heartland();
        $paymentDetails->setRequest($request);
        $paymentDetails->setOrder($order);
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
     * @param Heartland $paymentDetails
     * @param string  $token
     * @return $this
     */
    protected function addToken(Heartland $paymentDetails, $token)
    {
        $tokenToCharge = new TokenToCharge();
        $order = $paymentDetails->getOrder();
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
     * @param Heartland $paymentDetails
     * @return BinaryMaskStatusRequest
     */
    protected function execute(Heartland $paymentDetails)
    {
        $captureRequest = new CaptureRequest($paymentDetails);
        $this->payum->execute($captureRequest);

        /** @var Heartland $model */
        $model = $captureRequest->getModel();
        $statusRequest = new BinaryMaskStatusRequest($model);
        $this->payum->execute($statusRequest);

        return $statusRequest;
    }
}
