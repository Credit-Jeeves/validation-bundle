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
        if($mobile){
            $attributes =  new AttributeGeneratorMobile();
        }else{
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
        $pageVars= array(
            'paymentType' => $paymentType->createView(),
            'paymentBalanceOnlyType' => $paymentBalanceOnlyType->createView(),
            'userDetailsType' => $userDetailsType->createView(),
            'questionsType' => $questionsType->createView(),
        );
        if($mobile){
            return $this->render('RjCheckoutBundle:Component:pay.mobile.html.twig', $pageVars); //mobile template override
        }else{
            //return $this->render('RjCheckoutBundle:Component:pay.mobile.html.twig', $pageVars); //mobile template override
            //return $this->render('RjCheckoutBundle:Component:pay.html.twig', $pageVars); //mobile template override
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

        $pageVars= array(
            'paymentAccountType' => $paymentAccountType->createView(),
            'addressesJson' => $addressesJson,
            'payAccountsJson' => $payAccountsJson,
        );

        if($mobile){
            return $this->render('RjCheckoutBundle:Component:source.mobile.html.twig', $pageVars); //mobile template override
        }else{
            return $pageVars;
        }
    }
}