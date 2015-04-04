<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\ACI\AciCollectPay;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Payum\Core\Payment as PaymentProcessor;

abstract class AbstractManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var PaymentProcessor
     */
    protected $paymentProcessor;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "payum" = @DI\Inject("payum")
     * })
     */
    public function __construct(EntityManager $em, $payum)
    {
        $this->em = $em;

        $this->paymentProcessor = $payum->getPayment('aci_collect_pay');
    }
}
