<?php

namespace RentJeeves\CheckoutBundle\Controller;

use CreditJeeves\CheckoutBundle\Form\Type\UserAddressType;
use CreditJeeves\DataBundle\Entity\Address;
use Doctrine\Common\Collections\ArrayCollection;
use Payum\Request\CaptureRequest;
use RentJeeves\CheckoutBundle\Form\Type\PaymentDetailsType;
use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType;
use RentJeeves\CheckoutBundle\Form\Type\UserDetailsType;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @method \RentJeeves\DataBundle\Entity\Tenant getUser()
 */
class ComponentController extends Controller
{
    /**
     * @Template()
     */
    public function payAction()
    {
        $paymentDetailsType = $this->createForm(new PaymentDetailsType());
        $userDetailsType = $this->createForm(new UserDetailsType($this->getUser()), $this->getUser());
        return array(
            'paymentDetailsType' => $paymentDetailsType->createView(),
            'userDetailsType' => $userDetailsType->createView()
        );
    }

    /**
     * @Template()
     */
    public function sourceAction()
    {
        $paymentAccountType = $this->createForm(new PaymentAccountType($this->getUser()));
        return array(
            'paymentAccounts' => $this->getUser()->getPaymentAccounts(),
            'paymentAccountType' => $paymentAccountType->createView(),
            'addresses' => $this->getUser()->getAddresses(),
        );
    }
}
