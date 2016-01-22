<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay;

use ACI\Client\CollectPay\Enum\LeastCostRouteType;
use Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry as PayumAwareRegistry;
use Psr\Log\LoggerInterface;
use Payum\Core\Payment as PaymentProcessor;
use Payum\AciCollectPay\Model\LeastCostRoute;
use Payum\AciCollectPay\Request\LeastCostRouteRequest\CheckLeastCostRoute;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidCardNumber;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException;
use RentJeeves\DataBundle\Enum\PaymentAccountType;

class LeastCostRouteManager
{
    /**
     * @var PaymentProcessor
     */
    protected $paymentProcessor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param PayumAwareRegistry $payum
     * @param LoggerInterface $logger
     */
    public function __construct(PayumAwareRegistry $payum, LoggerInterface $logger)
    {
        $this->paymentProcessor = $payum->getPayment('aci_collect_pay');
        $this->logger = $logger;
    }

    /**
     * @param $cardNumber
     * @return string
     * @throws \Exception
     */
    public function getLeastCostRoute($cardNumber)
    {
        $requestModel = new LeastCostRoute();

        $requestModel->setCardNumber($cardNumber);

        $request = new CheckLeastCostRoute($requestModel);

        try {
            $this->paymentProcessor->execute($request);
        } catch (\Exception $e) {
            $this->logger->alert(
                sprintf(
                    '[ACI CollectPay LCR Exception]:%s:%s',
                    $cardNumber,
                    $e->getMessage()
                )
            );
            throw $e;
        }

        if (!$request->getIsSuccessful()) {
            $this->logger->alert(
                sprintf(
                    '[ACI CollectPay LCR Error]:%s:%s',
                    $cardNumber,
                    $request->getMessages()
                )
            );
            throw new PaymentProcessorRuntimeException($request->getMessages());
        }

        switch ($request->getModel()->getLeastCostRouting()) {
            case LeastCostRouteType::CREDIT_CARD:
                return PaymentAccountType::CARD;
            case LeastCostRouteType::DEBIT_CARD:
                return PaymentAccountType::DEBIT_CARD;
            default:
                throw PaymentProcessorInvalidCardNumber::invalidCardNumber($cardNumber);
        }
    }
}
