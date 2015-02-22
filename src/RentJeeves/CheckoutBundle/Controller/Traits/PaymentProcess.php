<?php
namespace RentJeeves\CheckoutBundle\Controller\Traits;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use Payum\Payment;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorInterface;
use RentJeeves\DataBundle\Entity\UserAwareInterface;
use RentJeeves\DataBundle\Entity\GroupAwareInterface;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\ContractStatus;
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
     * Creates a new payment account. Right now only Heartland is supported.
     *
     * @param Form $paymentAccountType
     * @param User $user
     * @param Group $group
     * @return mixed
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

        $paymentAccountMapped = $this->get('payment_account.type.mapper')->map($paymentAccountType);
        /** @var PaymentProcessorInterface $paymentProcessor */
        $paymentProcessor = $this->get('payment_processor.factory')->getPaymentProcessor($group);
        $token = $paymentProcessor->createPaymentAccount($paymentAccountMapped, $user, $group);

        $paymentAccountEntity->setToken($token);

        if ($paymentAccountEntity instanceof UserAwareInterface) {
            $paymentAccountEntity->setUser($user);
        }

        $em->persist($paymentAccountEntity);
        $em->flush();

        return $paymentAccountEntity;
    }

    protected function savePayment(
        Request $request,
        Form $form,
        $contract,
        $paymentAccount,
        $recurring,
        $pidkiqEnabled
    ) {
        $em = $this->getDoctrine()->getManager();

        /** @var Payment $paymentEntity */
        $paymentEntity = $form->getData();

        /**
         * @var Contract $contract
         */
        if ($contract) {
            $paymentEntity->setContract($contract);
        } else {
            throw $this->createNotFoundException('Contract cannot be null');
        }

        /**
         * @var PaymentAccount $paymentAccount
         */
        if ($paymentAccount) {
            $paymentEntity->setPaymentAccount($paymentAccount);
        } else {
            throw $this->createNotFoundException('PaymentAccount cannot be null');
        }

        if ($pidkiqEnabled && !$this->isVerifiedUser($request, $contract)) {
            throw $this->createNotFoundException('User verification failed');
        }

        if ($recurring) {
            $paymentEntity->setEndMonth(null);
            $paymentEntity->setEndYear(null);
        }

        /*
         * the user only specifies the year and month, so here we set the day
         * based on the contract due date. If no due date specified we default to
         * the first day of the month.
         */
        if ($paymentEntity->getPaidFor()) {
            $paymentEntity->setPaidFor($this->mergePaidAndDueDates($paymentEntity->getPaidFor(), $contract));
        }

        if ($contract->getStatus() === ContractStatus::INVITE) {
            $contract->setStatus(ContractStatus::APPROVED);
        }
        $em->persist($contract);
        $em->persist($paymentEntity);
        $em->flush();
    }

    private function mergePaidAndDueDates(DateTime $paidFor, $contract)
    {
        $dueDay = ($contract->getDueDate()) ? $contract->getDueDate() : 1;

        return $paidFor->setDate($paidFor->format('Y'), $paidFor->format('m'), $dueDay);
    }

    protected function isVerifiedUser(Request $request, Contract $contract)
    {
        $setting = $contract->getGroup()->getGroupSettings();
        if ($setting->getIsPidVerificationSkipped()) {
            return true;
        }
        $session = $request->getSession();
        $isValidUser = $session->get('isValidUser', false);
        if (UserIsVerified::PASSED === $this->getUser()->getIsVerified() || $isValidUser) {
            return true;
        }

        return false;
    }

    protected function hasNewAddress(Form $paymentAccountType)
    {
        return $paymentAccountType->has('is_new_address') ?
            $paymentAccountType->get('is_new_address')->getData() === "true" : false;
    }
}
