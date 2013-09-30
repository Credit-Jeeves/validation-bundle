<?php
namespace RentJeeves\CheckoutBundle\Controller;

use CreditJeeves\CheckoutBundle\Form\Type\UserAddressType;
use CreditJeeves\DataBundle\Entity\Address;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use Doctrine\Common\Collections\ArrayCollection;
use Payum\Heartland\Bridge\Doctrine\Entity\PaymentDetails;
use Payum\Heartland\Soap\Base\ACHAccountType;
use Payum\Heartland\Soap\Base\ACHDepositType;
use Payum\Heartland\Soap\Base\GetTokenRequest;
use Payum\Heartland\Soap\Base\GetTokenResponse;
use Payum\Heartland\Soap\Base\TokenPaymentMethod;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;
use RentJeeves\CheckoutBundle\Form\Type\PaymentType;
use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType;
use RentJeeves\CheckoutBundle\Form\Type\UserDetailsType;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;
use RentJeeves\DataBundle\Enum\PaymentAccountType AS PaymentAccountTypeEnum;
use \DateTime;

/**
 * @method \RentJeeves\DataBundle\Entity\Tenant getUser()
 * @Route("/checkout")
 */
class PayController extends Controller
{
    use FormErrors;

    /**
     * @Route("/payment", name="checkout_pay_payment", options={"expose"=true})
     * @Method({"POST"})
     */
    public function paymentAction(Request $request)
    {
        $paymentType = $this->createForm(new PaymentType());
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

        $paymentAccountType->handleRequest($request);
        if (!$paymentAccountType->isValid()) {
            return $this->renderErrors($paymentAccountType);
        }
        $em = $this->get('doctrine.orm.default_entity_manager');

        /** @var  $payment */
        $payment = $this->get('payum')->getPayment('heartland');
        $request = new GetTokenRequest();

        /** @var PaymentAccount $paymentAccountEntity */
        $paymentAccountEntity = $paymentAccountType->getData();
        $request->getAccountHolderData()->setEmail($this->getUser()->getEmail());
        $request->getAccountHolderData()->setFirstName($this->getUser()->getFirstName());
        $request->getAccountHolderData()->setLastName($this->getUser()->getLastName());
        $request->getAccountHolderData()->setPhone($this->getUser()->getPhone());
        if (PaymentAccountTypeEnum::CARD == $paymentAccountEntity->getType()) {
            $ccMonth = $paymentAccountType->get('ExpirationMonth')->getData();
            $ccYear = $paymentAccountType->get('ExpirationYear')->getData();
            $paymentAccountEntity->setCcExpiration(new DateTime("last day of {$ccYear}-{$ccMonth}"));
            $paymentAccountEntity->getAddress()->setUser($this->getUser());
            /** @var Address $address */
            if ($address = $paymentAccountType->get('address_choice')->getData()) {
                $paymentAccountEntity->setAddress($address);
            }
            $request->getAccountHolderData()->setAddress($paymentAccountEntity->getAddress()->getAddress());
            $request->getAccountHolderData()->setCity($paymentAccountEntity->getAddress()->getCity());
            $request->getAccountHolderData()->setState($paymentAccountEntity->getAddress()->getArea());
            $request->getAccountHolderData()->setZip($paymentAccountEntity->getAddress()->getZip());

            $request->setAccountNumber($paymentAccountType->get('CardNumber')->getData());
            $request->setExpirationMonth($ccMonth);
            $request->setExpirationYear($ccYear);
            $request->setPaymentMethod(TokenPaymentMethod::CREDIT);
        } elseif (PaymentAccountTypeEnum::BANK == $paymentAccountEntity->getType()) {
            $paymentAccountEntity->setAddress(null);
            $request->setRoutingNumber($paymentAccountType->get('RoutingNumber')->getData());
            $request->setAccountNumber($paymentAccountType->get('AccountNumber')->getData());
            $ACHDepositType = $paymentAccountType->get('ACHDepositType')->getData();
            $request->setACHDepositType($ACHDepositType);
            if (ACHDepositType::UNASSIGNED == $ACHDepositType) {
                $request->setACHAccountType(ACHAccountType::BUSINESS);
            } else {
                $request->setACHAccountType(ACHAccountType::PERSONAL);
            }
            $request->setPaymentMethod(TokenPaymentMethod::ACH);
        }

        $merchantName = null;
        $group = null;
        /** @var Group $group */
        if ($group = $em->getRepository('DataBundle:Group')->find($paymentAccountType->get('groupId')->getData())) {
            $merchantName = $group->getMerchantName();
        }
        $paymentAccountEntity->setGroup($group);

        if (empty($merchantName)) {
            return new JsonResponse(
                array(
                    $paymentAccountType->getName() => array(
                        '_globals' => array(
                            'Merchant name not installed'
                        )
                    )
                )
            );
        }

        $paymentDetails = new PaymentDetails();
        $paymentDetails->setMerchantName($merchantName);
        $paymentDetails->setRequest($request);

        $captureRequest = new CaptureRequest($paymentDetails);
        $payment->execute($captureRequest);

        $statusRequest = new BinaryMaskStatusRequest($captureRequest->getModel());
        $payment->execute($statusRequest);

        /** @var GetTokenResponse $response */
        $response = $statusRequest->getModel()->getResponse();

        $paymentAccountEntity->setUser($this->getUser());
        if ($statusRequest->isSuccess()) {
            $paymentAccountEntity->setToken($response->getToken());
        } else {
            return new JsonResponse(
                array(
                    $paymentAccountType->getName() => array(
                        '_globals' => explode('|', $paymentDetails->getMessages())
                    )
                )
            );
        }

        $em->persist($paymentAccountEntity);
        $em->flush();

        return new JsonResponse(
            array(
                'success' => true,
                'paymentAccountId' => $paymentAccountEntity->getId()
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
        $em = $this->get('doctrine.orm.default_entity_manager');

        /** @var Address $address */
        $address = null;
        $em->getRepository('DataBundle:Address')->resetDefaults($this->getUser()->getId());
        /** @var Address $addressChose */
        /** @var Address $newAddress */
        if ($addressChose = $userType->get('address_choice')->getData()) {
            $address = $addressChose;
        } elseif ($newAddress = $userType->get('new_address')->getData()) {
            $address = $newAddress;
            $address->setUser($this->getUser());
            $address->setIsDefault(1);
        }

        $data = $userType->getData();
        $em->persist($address);
        $em->persist($data);
        $em->flush();

        return new JsonResponse(
            array(
                'success' => true
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
            return $this->createNotFoundException('Verification not passed');
        }
        $paymentType = $this->createForm(new PaymentType());
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

        $em->persist($paymentEntity);
        $em->flush($paymentEntity);

        return new JsonResponse(
            array(
                'success' => true
            )
        );
    }
}
