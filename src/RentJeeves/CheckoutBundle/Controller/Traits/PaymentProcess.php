<?php
namespace RentJeeves\CheckoutBundle\Controller\Traits;

use CreditJeeves\DataBundle\Entity\Address;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\User;
use Payum\Payment;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;
use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType;
use RentJeeves\DataBundle\Entity\UserAwareInterface;
use RentJeeves\DataBundle\Entity\GroupAwareInterface;
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
     * @param Form $paymentAccountType
     *
     * @return JsonResponse
     */
    protected function savePaymentAccount(Form $paymentAccountType, User $user, Group $group)
    {
        $em = $this->getDoctrine()->getManager();
        $paymentAccountEntity = $paymentAccountType->getData();

        if ($paymentAccountEntity instanceof GroupAwareInterface) {
            // if the account can have the group set directly, then set it
            $paymentAccountEntity->setGroup($group);
        } else {
            // otherwise add the the associated depositAccount
            $depositAccount = $em->getRepository('RjDataBundle:DepositAccount')->findOneByGroup($group);

            // make sure this deposit account is added only once!
            if (!$paymentAccountEntity->getDepositAccounts()->contains($depositAccount)) {
                $paymentAccountEntity->addDepositAccount($depositAccount);
            }
        }

        $merchantName = $this->getMerchantName($group);

        if (empty($merchantName)) {
            throw new RuntimeException('Merchant name is not installed');
        }

        $paymentAccountMapped = $this->get('payment_account.type.mapper')->map($paymentAccountType);
        $tokenRequest = $this->get('payment.account')->getTokenRequest($paymentAccountMapped, $user);
        $token = $this->get('payment.account')->getTokenResponse($tokenRequest, $merchantName);

        $paymentAccountEntity->setToken($token);

        if ($paymentAccountEntity instanceof UserAwareInterface) {
            $paymentAccountEntity->setUser($user);
        }

        $em->persist($paymentAccountEntity);
        $em->flush();

        return $paymentAccountEntity;
    }
}
