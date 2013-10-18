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
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;

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

        return $this->savePaymentAccount($paymentAccountType);
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
        $em = $this->get('doctrine.orm.default_entity_manager');

        /** @var Address $address */
        $address = null;
        $isNewAddress = false;
        $em->getRepository('DataBundle:Address')->resetDefaults($this->getUser()->getId());
        /** @var Address $addressChose */
        /** @var Address $newAddress */
        if ($addressChose = $userType->get('address_choice')->getData()) {
            $address = $addressChose;
        } elseif ($newAddress = $userType->get('new_address')->getData()) {
            $address = $newAddress;
            $address->setUser($this->getUser());
            $isNewAddress = true;
        }
        $address->setIsDefault(1);
        $data = $userType->getData();
        $em->persist($address);
        $em->persist($data);
        $em->flush();

        return new JsonResponse(
            array(
                'success' => true,
                'newAddress' => $isNewAddress ?
                    $this->get('jms_serializer')->serialize(
                        $address,
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
        if (UserIsVerified::PASSED != $this->getUser()->getIsVerified()) {
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


        $em->persist($paymentEntity);
        $em->flush($paymentEntity);

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
