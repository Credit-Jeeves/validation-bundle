<?php
namespace RentJeeves\CheckoutBundle\Controller\Traits;

use CreditJeeves\DataBundle\Entity\Address;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\User;
use Payum\Heartland\Soap\Base\ACHAccountType;
use Payum\Heartland\Soap\Base\ACHDepositType;
use Payum\Heartland\Soap\Base\GetTokenRequest;
use Payum\Heartland\Soap\Base\GetTokenResponse;
use Payum\Heartland\Soap\Base\TokenPaymentMethod;
use Payum\Payment;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;
use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\Heartland as PaymentDetails;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use RentJeeves\DataBundle\Entity\UserAwareInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use \DateTime;
use \RuntimeException;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 *
 * @method mixed get()
 * @method array renderErrors()
 * @method \Doctrine\Bundle\DoctrineBundle\Registry getDoctrine()
 * @method \RentJeeves\DataBundle\Entity\Tenant getUser()
 */
trait PaymentProcess
{
    protected $hasNewAddress = false;
    protected $merchantName = null;

    protected function setMerchantName($merchantName)
    {
        $this->merchantName = $merchantName;
    }

    protected function getMerchantName(Group $group)
    {
        return $this->merchantName ?: $group->getMerchantName();
    }

    /**
     * @param PaymentAccountType $paymentAccountType
     *
     * @return JsonResponse
     */
    protected function savePaymentAccount(Form $paymentAccountType, User $user, $group = null)
    {
        $em = $this->getDoctrine()->getManager();
        $paymentAccountEntity = $paymentAccountType->getData();

        if (empty($group)) {
            $group = $em->getRepository('DataBundle:Group')->find($paymentAccountType->get('groupId')->getData());
        }

        $paymentAccountEntity->setGroup($group);

        $merchantName = $this->getMerchantName($group);

        if (empty($merchantName)) {
            throw new RuntimeException('Merchant name is not installed');
        }

        $tokenRequest = $this->getTokenRequest($paymentAccountType, $user);
        $token = $this->getTokenResponse($tokenRequest, $merchantName);

        $paymentAccountEntity->setToken($token);

        if ($paymentAccountEntity instanceof UserAwareInterface) {
            $paymentAccountEntity->setUser($user);
        }

        $em->persist($paymentAccountEntity);
        $em->flush();

        return $paymentAccountEntity;
    }

    protected function getTokenRequest(Form $paymentAccountType, User $user)
    {
        $request = new GetTokenRequest();

        /** @var PaymentAccount $paymentAccountEntity */
        $paymentAccountEntity = $paymentAccountType->getData();
        $request->getAccountHolderData()->setEmail($user->getEmail());
        $request->getAccountHolderData()->setFirstName($user->getFirstName());
        $request->getAccountHolderData()->setLastName($user->getLastName());
        $request->getAccountHolderData()->setPhone($user->getPhone());

        if (PaymentAccountTypeEnum::CARD == $paymentAccountEntity->getType()) {
            $ccMonth = $paymentAccountType->get('ExpirationMonth')->getData();
            $ccYear = $paymentAccountType->get('ExpirationYear')->getData();
            $paymentAccountEntity->setCcExpiration(new DateTime("last day of {$ccYear}-{$ccMonth}"));

            /** @var Address $address */
            if ($address = $paymentAccountType->get('address_choice')->getData()) {
                // TODO: address is a Proxy, but should be Entity
                $paymentAccountEntity->setAddress($address);
            } else {
                $this->hasNewAddress = true;
            }
            $paymentAccountEntity->getAddress()->setUser($user);

            $request->getAccountHolderData()->setNameOnCard($paymentAccountType->get('CardAccountName')->getData());
            $request->getAccountHolderData()->setAddress($paymentAccountEntity->getAddress()->getAddress());
            $request->getAccountHolderData()->setCity($paymentAccountEntity->getAddress()->getCity());
            $request->getAccountHolderData()->setState($paymentAccountEntity->getAddress()->getArea());
            $request->getAccountHolderData()->setZip($paymentAccountEntity->getAddress()->getZip());

            $request->setAccountNumber($paymentAccountType->get('CardNumber')->getData());
            $request->setExpirationMonth($ccMonth);
            $request->setExpirationYear($ccYear);
            $request->setPaymentMethod(TokenPaymentMethod::CREDIT);
        } elseif (PaymentAccountTypeEnum::BANK == $paymentAccountEntity->getType()) {

            if ($paymentAccountEntity instanceof UserAwareInterface) {
                $paymentAccountEntity->setAddress(null);
            }

            $fullName = trim($paymentAccountType->get('PayorName')->getData());
            $lastSpacePosition = strrpos($fullName, ' ');
            $firstName = substr($fullName, 0, $lastSpacePosition);
            $lastName = substr($fullName, $lastSpacePosition + 1);

            $request->getAccountHolderData()->setFirstName($firstName);
            $request->getAccountHolderData()->setLastName($lastName);
            $request->setRoutingNumber($paymentAccountType->get('RoutingNumber')->getData());
            $request->setAccountNumber($paymentAccountType->get('AccountNumber')->getData());
            $ACHDepositType = $paymentAccountType->get('ACHDepositType')->getData();

            if (ACHDepositType::UNASSIGNED == $ACHDepositType) {
                $request->setACHDepositType(ACHDepositType::CHECKING);
                $request->setACHAccountType(ACHAccountType::BUSINESS);
            } else {
                $request->setACHDepositType($ACHDepositType);
                $request->setACHAccountType(ACHAccountType::PERSONAL);
            }
            $request->setPaymentMethod(TokenPaymentMethod::ACH);
        }

        return $request;
    }

    protected function getTokenResponse($tokenRequest, $merchantName)
    {
        $paymentDetails = new PaymentDetails();
        $paymentDetails->setMerchantName($merchantName);
        $paymentDetails->setRequest($tokenRequest);
        $captureRequest = new CaptureRequest($paymentDetails);

        /** @var Payment $payment */
        $payment = $this->get('payum')->getPayment('heartland');
        $payment->execute($captureRequest);

        $statusRequest = new BinaryMaskStatusRequest($captureRequest->getModel());
        $payment->execute($statusRequest);

        /** @var GetTokenResponse $response */
        $response = $statusRequest->getModel()->getResponse();

        if (!$statusRequest->isSuccess()) {
            throw new RuntimeException($paymentDetails->getMessages());
        }

        return $response->getToken();
    }
}
