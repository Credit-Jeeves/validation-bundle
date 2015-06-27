<?php

namespace RentJeeves\CheckoutBundle;

use Payum\AciCollectPay\Bridge\Symfony\AciCollectPayPaymentFactory;
use Payum\AciPayAnyone\Bridge\Symfony\AciPayAnyonePaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RjCheckoutBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        /* @var  PayumExtension $payumExtension */
        $payumExtension = $container->getExtension('payum');

        $payumExtension->addPaymentFactory(new AciCollectPayPaymentFactory());
        $payumExtension->addPaymentFactory(new AciPayAnyonePaymentFactory());
    }
}
