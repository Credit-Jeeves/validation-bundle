<?php
namespace RentJeeves\CheckoutBundle\Controller\Traits;

use CreditJeeves\DataBundle\Entity\Address;
use CreditJeeves\DataBundle\Entity\Group;
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
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use \DateTime;

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
    /**
     * @param PaymentAccountType $paymentAccountType
     *
     * @return JsonResponse
     */
    protected function savePaymentAccount(Form $paymentAccountType)
    {
        $paymentAccountType->handleRequest($this->get('request'));
        if (!$paymentAccountType->isValid()) {
            return $this->renderErrors($paymentAccountType);
        }
        $em = $this->getDoctrine()->getManager();

        /** @var Payment $payment */
        $payment = $this->get('payum')->getPayment('heartland');
        $request = new GetTokenRequest();

        $isNewAddress = false;

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

            /** @var Address $address */
            if ($address = $paymentAccountType->get('address_choice')->getData()) {
//                /** @var Address $ad */
//                foreach ($this->getUser()->getAddresses() as $ad) { // TODO find a problem and remove
//                    if ($address->getId() == $ad->getId()) {
//                        $address = $ad;
//                        var_dump($ad->getZip());
//                        break;
//                    }
//                }
                $paymentAccountEntity->setAddress($address);
            } else {
                $isNewAddress = true;
            }
            $paymentAccountEntity->getAddress()->setUser($this->getUser());


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

            if (ACHDepositType::UNASSIGNED == $ACHDepositType) {
                $request->setACHDepositType(ACHDepositType::CHECKING);
                $request->setACHAccountType(ACHAccountType::BUSINESS);
            } else {
                $request->setACHDepositType($ACHDepositType);
                $request->setACHAccountType(ACHAccountType::PERSONAL);
            }
            $request->setPaymentMethod(TokenPaymentMethod::ACH);
        }

        $merchantName = null;
        $group = $paymentAccountEntity->getGroup();
        if (empty($group)) {
            /** @var Group $group */
            if ($group = $em->getRepository('DataBundle:Group')->find($paymentAccountType->get('groupId')->getData())) {
                $merchantName = $group->getMerchantName();
            }
            $paymentAccountEntity->setGroup($group);
        } else {
            $merchantName = $group->getMerchantName();
        }

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
                'paymentAccount' => $this->get('jms_serializer')->serialize(
                    $paymentAccountEntity,
                    'array'
                ),
                'newAddress' => $isNewAddress ?
                    $this->get('jms_serializer')->serialize(
                        $paymentAccountEntity->getAddress(),
                        'array'
                    ) : null
            )
        );
    }
}
