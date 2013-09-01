<?php

namespace RentJeeves\CheckoutBundle\Controller;

use Payum\Request\CaptureRequest;
use RentJeeves\CheckoutBundle\Form\Type\PaymentDetailsType;
use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ComponentController extends Controller
{
    /**
     * @Template()
     */
    public function payAction()
    {
        $paymentDetailsType = $this->createForm(new PaymentDetailsType());
        return array(
            'paymentDetailsType' => $paymentDetailsType->createView()
        );
    }

    /**
     * @Template()
     */
    public function sourceAction()
    {
        $paymentAccountType = $this->createForm(new PaymentAccountType());
        return array(
            'paymentAccountType' => $paymentAccountType->createView(),
        );
    }
}
