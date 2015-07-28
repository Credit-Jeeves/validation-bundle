<?php

namespace RentJeeves\CheckoutBundle\Controller;

use CreditJeeves\ExperianBundle\Form\Type\QuestionsType;
use JMS\Serializer\SerializationContext;
use RentJeeves\CheckoutBundle\Form\Type\PaymentBalanceOnlyType;
use RentJeeves\CheckoutBundle\Form\Type\PaymentType;
use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType;
use RentJeeves\CheckoutBundle\Form\Type\UserDetailsType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\CheckoutBundle\Form\AttributeGenerator\AttributeGeneratorWeb;
use RentJeeves\CheckoutBundle\Form\AttributeGenerator\AttributeGeneratorMobile;

/**
 * @method \RentJeeves\DataBundle\Entity\Tenant getUser()
 */
class ComponentController extends Controller
{
    /**
     * @Template()
     */
    public function payAction($mobile = false)
    {
        if ($mobile) {
            $attributes =  new AttributeGeneratorMobile();
        } else {
            $attributes =  new AttributeGeneratorWeb();
        }

        $paymentType = $this->createForm(
            new PaymentType(
                $this->container->getParameter('payment_one_time_until_value'),
                array(),
                array(),
                0,
                0,
                $attributes
            )
        );
        $paymentBalanceOnlyType =  $this->createForm(
            new PaymentBalanceOnlyType(
                $this->container->getParameter('payment_one_time_until_value'),
                array(),
                array(),
                $this->getDoctrine()->getManager(),
                0,
                0,
                $attributes,
                $this->get('translator')
            )
        );
        $userDetailsType = $this->createForm(new UserDetailsType($this->getUser()), $this->getUser());
        $questionsType = $this->createForm(
            new QuestionsType(
                [
                    [],
                    [],
                    [],
                    [],
                    [],
                    [],
                ]
            )
        );

        $pageVars = [
            'paymentType' => $paymentType->createView(),
            'paymentBalanceOnlyType' => $paymentBalanceOnlyType->createView(),
            'userDetailsType' => $userDetailsType->createView(),
            'questionsType' => $questionsType->createView(),
            'isLocked' => $this->getDoctrine()->getManager()->getRepository("RjDataBundle:Contract")
                ->isPaymentProcessorLocked($this->getUser())
        ];
        if ($mobile) {
            return $this->render('RjCheckoutBundle:Component:pay.mobile.html.twig', $pageVars);
        } else {
            return $pageVars;
        }
    }

    /**
     * @Template()
     */
    public function sourceAction($mobile = false)
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

        $pageVars = array(
            'paymentAccountType' => $paymentAccountType->createView(),
            'addressesJson' => $addressesJson,
            'payAccountsJson' => $payAccountsJson,
        );

        if ($mobile) {
            return $this->render('RjCheckoutBundle:Component:source.mobile.html.twig', $pageVars);
        } else {
            return $pageVars;
        }
    }
}
