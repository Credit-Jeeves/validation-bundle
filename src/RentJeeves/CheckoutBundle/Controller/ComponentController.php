<?php

namespace RentJeeves\CheckoutBundle\Controller;

use CreditJeeves\ExperianBundle\Form\Type\QuestionsType;
use RentJeeves\CheckoutBundle\Form\Type\PaymentBalanceOnlyType;
use RentJeeves\CheckoutBundle\Form\Type\PaymentType;
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
            new PaymentType(),
            null,
            [
                'one_time_until_value' => $this->container->getParameter('payment_one_time_until_value'),
                'attributes' => $attributes,
            ]
        );
        $paymentBalanceOnlyType =  $this->createForm(
            new PaymentBalanceOnlyType(),
            null,
            [
                'one_time_until_value' => $this->container->getParameter('payment_one_time_until_value'),
                'attributes' => $attributes,
                'em' => $this->getDoctrine()->getManager(),
            ]
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
            'isLocked' => $this->getDoctrine()->getManager()->getRepository('RjDataBundle:Tenant')
                ->isPaymentProcessorLocked($this->getUser())
        ];
        if ($mobile) {
            return $this->render('RjCheckoutBundle:Component:pay.mobile.html.twig', $pageVars);
        } else {
            return $pageVars;
        }
    }
}
