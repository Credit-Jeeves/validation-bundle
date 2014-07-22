<?php

namespace RentJeeves\CheckoutBundle\Controller;

use CreditJeeves\CheckoutBundle\Form\Type\UserAddressType;
use CreditJeeves\DataBundle\Entity\Address;
use CreditJeeves\ExperianBundle\Form\Type\QuestionsType;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\SerializationContext;
use Payum\Request\CaptureRequest;
use RentJeeves\CheckoutBundle\Form\Type\PaymentBalanceOnlyType;
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
        $paymentType = $this->createForm(
            new PaymentType(
                $this->container->getParameter('payment_one_time_until_value'),
                array(),
                array()
            )
        );
        $paymentBalanceOnlyType =  $this->createForm(
            new PaymentBalanceOnlyType(
                $this->container->getParameter('payment_one_time_until_value'),
                array(),
                array()
            )
        );
        $userDetailsType = $this->createForm(new UserDetailsType($this->getUser()), $this->getUser());
        $questionsType = $this->createForm(
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
            'paymentType' => $paymentType->createView(),
            'paymentBalanceOnlyType' => $paymentBalanceOnlyType->createView(),
            'userDetailsType' => $userDetailsType->createView(),
            'questionsType' => $questionsType->createView(),
        );
    }

    /**
     * @Template()
     */
    public function sourceAction()
    {
        $paymentAccountType = $this->createForm(new PaymentAccountType($this->getUser()));

        $this->get('soft.deleteable.control')->enable();

        $payAccountsJson = $this->get('jms_serializer')->serialize(
            $this->getUser()->getPaymentAccounts(),
            'json',
            SerializationContext::create()->setGroups(array('paymentAccounts'))
        );

        $addressesJson = $this->get('jms_serializer')->serialize(
            $this->getUser()->getAddresses(),
            'json',
            SerializationContext::create()->setGroups(array('paymentAccounts'))
        );

        return array(
            'paymentAccountType' => $paymentAccountType->createView(),
            'addressesJson' => $addressesJson,
            'payAccountsJson' => $payAccountsJson,
        );
    }
}
