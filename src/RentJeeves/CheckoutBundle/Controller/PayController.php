<?php
namespace RentJeeves\CheckoutBundle\Controller;

use CreditJeeves\DataBundle\Entity\Address;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use Doctrine\Common\Collections\ArrayCollection;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;
use RentJeeves\CheckoutBundle\Form\Type\PaymentType;
use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType;
use RentJeeves\CheckoutBundle\Form\Type\UserDetailsType;
use RentJeeves\CheckoutBundle\Services\UserDetailsTypeProcessor;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\ContractStatus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;
use Exception;

/**
 * @method \RentJeeves\DataBundle\Entity\Tenant getUser()
 * @Route("/checkout")
 */
class PayController extends Controller
{
    use FormErrors;
    use Traits\PaymentProcess;

    protected function createPaymentForm()
    {
        $formType = new PaymentType($this->container->getParameter('payment_one_time_until_value'));
        $formData = $this->getRequest()->get($formType->getName());
        /** @var Payment $paymentEntity */
        $paymentEntity = null;
        if (!empty($formData['id'])) {
            $paymentEntity = $this->getDoctrine()
                ->getManager()
                ->getRepository('RjDataBundle:Payment')
                ->find($formData['id']);
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
        $paymentType = $this->createPaymentForm();
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

        try {
            $paymentAccountEntity = $this->savePaymentAccount($paymentAccountType, $this->getUser());
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
                    'array'
                ),
                'newAddress' => $this->hasNewAddress ?
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

    protected function isVerifiedUser($request)
    {
        if ($this->getUser()->getIsPidVerificationSkipped()) {
            return true;
        }
        $session = $request->getSession();
        $isValidUser = $session->get('isValidUser', false);
        if (UserIsVerified::PASSED === $this->getUser()->getIsVerified() || $isValidUser) {
            return true;
        }

        return false;
    }

    /**
     * @Route("/exec", name="checkout_pay_exec", options={"expose"=true})
     * @Method({"POST"})
     */
    public function execAction(Request $request)
    {

        if (!$this->isVerifiedUser($request)) {
            throw $this->createNotFoundException('Verification not passed');
        }

        $paymentType = $this->createPaymentForm();
        $paymentType->handleRequest($request);
        if (!$paymentType->isValid()) {
            return $this->renderErrors($paymentType);
        }

        $em = $this->get('doctrine.orm.default_entity_manager');

        /** @var Payment $paymentEntity */
        $paymentEntity = $paymentType->getData();

        if ($contract = $em->getRepository('RjDataBundle:Contract')
                ->find($paymentType->get('contractId')->getData())
        ) {
            $paymentEntity->setContract($contract);
        } else {
            throw $this->createNotFoundException('Contract does not exist');
        }

        if ($paymentAccount = $em->getRepository('RjDataBundle:PaymentAccount')
                ->find($paymentType->get('paymentAccountId')->getData())
        ) {
            $paymentEntity->setPaymentAccount($paymentAccount);
        }
        if ('on' != $paymentType->get('ends')->getData()) {
            $paymentEntity->setEndMonth(null);
            $paymentEntity->setEndYear(null);
        }

        $contract->setStatus(ContractStatus::APPROVED);
        $em->persist($contract);
        $em->persist($paymentEntity);
        $em->flush();

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

        $payment->setStatus(PaymentStatus::CLOSE);
        $em->persist($payment);
        $em->flush($payment);
        return $this->redirect($request->headers->get('referer'));
    }
}
