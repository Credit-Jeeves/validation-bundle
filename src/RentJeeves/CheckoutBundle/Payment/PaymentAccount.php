<?php

namespace RentJeeves\CheckoutBundle\Payment;

use Payum\Heartland\Soap\Base\ACHAccountType;
use Payum\Heartland\Soap\Base\ACHDepositType;
use Payum\Heartland\Soap\Base\GetTokenRequest;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;
use CreditJeeves\DataBundle\Entity\Address;
use RentJeeves\DataBundle\Entity\UserAwareInterface;
use Symfony\Component\Form\Form;
use CreditJeeves\DataBundle\Entity\User;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use RentJeeves\CoreBundle\DateTime;
use Payum\Heartland\Soap\Base\TokenPaymentMethod;
use RentJeeves\DataBundle\Entity\Heartland as PaymentDetails;
use \RuntimeException;

class PaymentAccount
{
    protected $payum;

    public function setPayum($payum)
    {
        $this->payum = $payum;
    }

    public function getPayum()
    {
        return $this->payum;
    }

    public function getTokenRequest(Form $paymentAccountType, User $user)
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
                $request->setACHAccountType(ACHAccountTypee::BUSINESS);
            } else {
                $request->setACHDepositType($ACHDepositType);
                $request->setACHAccountType(ACHAccountType::PERSONAL);
            }
            $request->setPaymentMethod(TokenPaymentMethod::ACH);
        }

        return $request;
    }

    public function getTokenResponse($tokenRequest, $merchantName)
    {
        $paymentDetails = new PaymentDetails();
        $paymentDetails->setMerchantName($merchantName);
        $paymentDetails->setRequest($tokenRequest);
        $captureRequest = new CaptureRequest($paymentDetails);

        /** @var Payment $payment */
        $payment = $this->getPayum()->getPayment('heartland');
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
