<?php
namespace RentJeeves\CheckoutBundle\Controller;

use RentJeeves\CheckoutBundle\Form\Type\PaymentBalanceOnlyType;
use RentJeeves\CheckoutBundle\Form\Type\PaymentType;
use RentJeeves\DataBundle\Enum\PaymentCloseReason;
use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType;
use RentJeeves\CheckoutBundle\Form\Type\UserDetailsType;
use RentJeeves\CheckoutBundle\Services\UserDetailsTypeProcessor;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Payment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;
use JMS\Serializer\SerializationContext;
use Exception;

/**
 * @method \RentJeeves\DataBundle\Entity\Tenant getUser()
 * @Route("/checkout")
 */
class PayController extends Controller
{
    use FormErrors;
    use Traits\PaymentProcess;
    use Traits\AccountAssociate;

    protected function createPaymentForm(Request $request)
    {
        $contractId = $request->get('contract_id');
        $contract = $this->getDoctrine()
            ->getManager()
            ->getRepository('RjDataBundle:Contract')
            ->findOneWithOperationsOrders($contractId);

        if (empty($contract)) {
            throw $this->createNotFoundException("Contract with '{$contractId}' not found");
        }

        $payBalanceOnly = $contract->getGroup()->getGroupSettings()->getPayBalanceOnly();
        if ($payBalanceOnly) {
            $formData = $request->get(PaymentBalanceOnlyType::NAME);
        } else {
            $formData = $request->get(PaymentType::NAME);
        }

        /** @var Payment $paymentEntity */
        $paymentEntity = null;
        if (!empty($formData['id'])) {
            $paymentEntity = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('RjDataBundle:Payment')
                    ->findOneWithContractOrdersOperations($formData['id']);
            $contract = $paymentEntity->getContract();
        }

        if ($payBalanceOnly) {
            $formType = new PaymentBalanceOnlyType(
                $this->container->getParameter('payment_one_time_until_value'),
                array(),
                array(),
                $this->getDoctrine()->getManager(),
                $contract->getGroup()->getGroupSettings()->getOpenDate(),
                $contract->getGroup()->getGroupSettings()->getCloseDate(),
                $this->get('translator')
            );
        } else {
            $dueDays = $contract->getSettings()->getDueDays();
            $formType = new PaymentType(
                $this->container->getParameter('payment_one_time_until_value'),
                $this->container->get('checkout.paid_for')->getArray($contract),
                array_combine($dueDays, $dueDays),
                $contract->getGroup()->getGroupSettings()->getOpenDate(),
                $contract->getGroup()->getGroupSettings()->getCloseDate()
            );
        }
        if (!empty($paymentEntity) &&
            $paymentEntity->getPaymentAccount()->getUser()->getId() != $this->getUser()->getId()
        ) {
            throw $this->createNotFoundException("Payment with '{$formData['id']}' not found");
        }

        return $this->createForm($formType, $paymentEntity);
    }

    /**
     * @Route("/payment", name="checkout_pay_payment", options={"expose"=true})
     * @Method({"POST"})
     */
    public function paymentAction(Request $request)
    {
        $paymentType = $this->createPaymentForm($request);
        $paymentType->handleRequest($request);
        if (!$paymentType->isValid()) {
            return $this->renderErrors($paymentType);
        }
        
        return new JsonResponse(
            array(
                'success' => true
            )
        );
    }
    
    /**
     * @Route("/source_existing", name="checkout_pay_existing_source", options={"expose"=true})
     * @Method({"POST"})
     */
    public function sourceExistingAction(Request $request)
    {
        $formType = new PaymentAccountType($this->getUser());
        $formData = $this->getRequest()->get($formType->getName());

        $paymentAccountId = $formData['id'];
        $groupId = $formData['groupId'];

        $em = $this->getDoctrine()->getManager();
        $paymentAccount = $em->getRepository('RjDataBundle:PaymentAccount')->find($paymentAccountId);
        $group = $em->getRepository('DataBundle:Group')->find($groupId);

        // ensure group id is associated with payment account
        try {
            $this->ensureAccountAssociation($paymentAccount, $group);
        } catch (Exception $e) {
            return new JsonResponse(
                array(
                    $formType->getName() => array(
                        '_globals' => explode('|', $e->getMessage())
                    )
                )
            );
        }

        return new JsonResponse(
            array('success' => true)
        );
    }

    /**
     * @Route("/source", name="checkout_pay_source", options={"expose"=true})
     * @Method({"POST"})
     */
    public function sourceAction(Request $request)
    {
        $paymentAccountType = $this->createForm(new PaymentAccountType($this->getUser()));
        $paymentAccountType->handleRequest($this->get('request'));
        if (!$paymentAccountType->isValid()) {
            return $this->renderErrors($paymentAccountType);
        }

        $em = $this->get('doctrine.orm.default_entity_manager');
        /** @var Contract $contract */
        $contract = $em
            ->getRepository('RjDataBundle:Contract')
            ->find($paymentAccountType->get('contractId')->getData());

        try {
            $paymentAccountEntity = $this->savePaymentAccount($paymentAccountType, $contract);
        } catch (Exception $e) {
            return new JsonResponse(
                array(
                    $paymentAccountType->getName() => array(
                        '_globals' => explode('|', $e->getMessage())
                    )
                )
            );
        }

        return new JsonResponse(
            array(
                'success' => true,
                'paymentAccount' => $this->get('jms_serializer')->serialize(
                    $paymentAccountEntity,
                    'array',
                    SerializationContext::create()->setGroups(array('basic'))
                ),
                'newAddress' => $this->hasNewAddress($paymentAccountType) ?
                    $this->get('jms_serializer')->serialize(
                        $paymentAccountEntity->getAddress(),
                        'array'
                    ) : null
            )
        );
    }


    /**
     * @Route("/user", name="checkout_pay_user", options={"expose"=true})
     * @Method({"POST"})
     */
    public function userAction(Request $request)
    {
        $userType = $this->createForm(new UserDetailsType($this->getUser()), $this->getUser());

        $userType->handleRequest($request);
        if (!$userType->isValid()) {
            return $this->renderErrors($userType);
        }
        /** @var $formProcessor UserDetailsTypeProcessor */
        $formProcessor = $this->get('user.details.type.processor');
        $formProcessor->save($userType, $this->getUser());

        return new JsonResponse(
            array(
                'success' => true,
                'newAddress' => $formProcessor->getIsNewAddress() ?
                    $this->get('jms_serializer')->serialize(
                        $formProcessor->getAddress(),
                        'array'
                    ) : null
            )
        );
    }

    /**
     * @Route("/exec", name="checkout_pay_exec", options={"expose"=true})
     * @Method({"POST"})
     */
    public function execAction(Request $request)
    {
        $paymentType = $this->createPaymentForm($request);
        $paymentType->handleRequest($request);
        if (!$paymentType->isValid()) {
            return $this->renderErrors($paymentType);
        }

        $em = $this->getDoctrine()->getManager();

        /**
         * @var Contract $contract
         */
        $contractId = $paymentType->get('contractId')->getData();
        if (!$contract = $em->getRepository('RjDataBundle:Contract')->find($contractId)) {
            throw $this->createNotFoundException('Contract does not exist');
        }
        /**
         * @var PaymentAccount $paymentAccount
         */
        $accountId = $paymentType->get('paymentAccountId')->getData();
        if (!$paymentAccount = $em->getRepository('RjDataBundle:PaymentAccount')->find($accountId)) {
            throw $this->createNotFoundException('Payment account does not exist');
        }

        $recurring = false;
        $payBalanceOnly = $contract->getGroup()->getGroupSettings()->getPayBalanceOnly();
        if (!$payBalanceOnly && 'on' != $paymentType->get('ends')->getData()) {
            $recurring = true;
        }
        
        $this->savePayment(
            $request,
            $paymentType,
            $contract,
            $paymentAccount,
            $recurring,
            true            # verify user
        );

        return new JsonResponse(
            array(
                'success' => true
            )
        );

    }

    /**
     * @Route("/cancel/{id}", name="checkout_pay_cancel", options={"expose"=true})
     * @Method({"GET"})
     */
    public function cancelAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Payment $payment */
        $payment = $em->getRepository('RjDataBundle:Payment')->find($id);

        if (empty($payment) || $payment->getPaymentAccount()->getUser()->getId() != $this->getUser()->getId()) {
            throw $this->createNotFoundException("Payment with '{$id}' not found");
        }

        $payment->setClosed($this, PaymentCloseReason::USER_CANCELLED);
        $em->persist($payment);
        $em->flush($payment);
        return $this->redirect($request->headers->get('referer'));
    }
}
