<?php
namespace RentJeeves\CheckoutBundle\Payment;

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
use RentJeeves\DataBundle\Entity\Heartland;
use RuntimeException;

abstract class Pay
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
     * @var Order
     */
    protected $order;

    /**
     * @var Heartland
     */
    protected $paymentDetails;

    /**
     * @DI\InjectParams({
     *     "payum" = @DI\Inject("payum"),
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager")
     * })
     */
    public function __construct(ContainerAwareRegistry $payum, $em)
    {
        $this->payum = $payum->getPayment('heartland');
        $this->em = $em;
    }

    /**
     * @return $this
     */
    public function newOrder()
    {
        $this->order = null;
        $this->paymentDetails = null;
        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        if (null === $this->order) {
            $this->order = new Order();
        }
        return $this->order;
    }

    /**
     * @return Heartland
     */
    public function getPaymentDetails()
    {
        if (null === $this->paymentDetails) {
            if (null === $this->order) {
                throw new RuntimeException('You must set Order first');
            }

            $request = new MakePaymentRequest();

            $billTransaction = new BillTransaction();
            $billTransaction->setAmountToApplyToBill($this->order->getSum());
            $request->getBillTransactions()->setBillTransaction(array($billTransaction));

            $request->getTransaction()
                ->setAmount($this->order->getSum())
                ->setFeeAmount($this->order->getFee());

            $this->paymentDetails = new Heartland();
            $this->paymentDetails->setRequest($request);
            $this->paymentDetails->setOrder($this->order);
            $this->paymentDetails->setAmount($this->order->getSum() + $this->order->getFee());
        }
        return $this->paymentDetails;
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    public function addToken($token)
    {
        $tokenToCharge = new TokenToCharge();
        $tokenToCharge->setAmount($this->order->getSum());
        $tokenToCharge->setExpectedFeeAmount($this->order->getFee());
        $tokenToCharge->setCardProcessingMethod(CardProcessingMethod::UNASSIGNED);
        $tokenToCharge->setToken($token);

        $tokensToCharge = $this->getPaymentDetails()->getRequest()->getTokensToCharge()->getTokenToCharge();
        if (!is_array($tokensToCharge)) {
            $tokensToCharge = array();
        }
        $tokensToCharge[] = $tokenToCharge;
        $this->getPaymentDetails()->getRequest()->getTokensToCharge()->setTokenToCharge($tokensToCharge);

        return $this;
    }

    /**
     * @return BinaryMaskStatusRequest
     */
    protected function execute()
    {
        $paymentDetails = $this->getPaymentDetails();
        $captureRequest = new CaptureRequest($paymentDetails);
        $this->payum->execute($captureRequest);

        /** @var Heartland $model */
        $model = $captureRequest->getModel();
        $statusRequest = new BinaryMaskStatusRequest($model);
        $this->payum->execute($statusRequest);
        $this->order->addHeartland($paymentDetails);
        return $statusRequest;
    }
}
