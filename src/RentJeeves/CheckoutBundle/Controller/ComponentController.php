<?php

namespace RentJeeves\CheckoutBundle\Controller;

use CreditJeeves\CheckoutBundle\Form\Type\UserAddressType;
use CreditJeeves\DataBundle\Entity\Address;
use CreditJeeves\ExperianBundle\Form\Type\QuestionsType;
use Doctrine\Common\Collections\ArrayCollection;
use Payum\Request\CaptureRequest;
use RentJeeves\CheckoutBundle\Form\Type\PaymentType;
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
        $paymentType              = $this->createForm(
            new PaymentType($this->container->getParameter('payment_one_time_until_value'))
        );
        $userDetailsType          = $this->createForm(new UserDetailsType($this->getUser()), $this->getUser());
        $questionsType            = $this->createForm(
            new QuestionsType(
                array(
                    array(),
                    array(),
                    array(),
                    array(),
                    array(),
                    array(),
                )
            )
        );

        return array(
            'paymentType'              => $paymentType->createView(),
            'userDetailsType'          => $userDetailsType->createView(),
            'questionsType'            => $questionsType->createView(),
        );
    }

    /**
     * @Template()
     */
    public function sourceAction()
    {
        $paymentAccountType = $this->createForm(new PaymentAccountType($this->getUser()));

        $this->get('soft.deleteable.control')->enable();

        return array(
            'paymentAccountType' => $paymentAccountType->createView(),
            'addresses' => $this->getUser()->getAddresses(),
            'paymentAccounts' => $this->getUser()->getPaymentAccounts(),
        );
    }
}
