<?php
namespace RentJeeves\CheckoutBundle\Controller\Traits;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\CheckoutBundle\PaymentProcessor\SubmerchantProcessorInterface;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\BillingAccount;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use \DateTime;

/**
 * @method mixed get()
 * @method array renderErrors()
 * @method \Doctrine\Bundle\DoctrineBundle\Registry getDoctrine()
 * @method \RentJeeves\DataBundle\Entity\Tenant|\RentJeeves\DataBundle\Entity\Landlord getUser()
 */
trait PaymentProcess
{
    /**
     * Creates a new payment account. Right now only Heartland is supported.
     *
     * @param  Form $paymentAccountType
     * @param  Contract $contract
     * @param  string $depositAccountType
     * @return mixed
     */
    protected function savePaymentAccount(
        Form $paymentAccountType,
        Contract $contract,
        $depositAccountType = DepositAccountType::RENT
    ) {
        $group = $contract->getGroup();

        /** @var \RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount $paymentAccountMapped */
        $paymentAccountMapped = $this->get('payment_account.type.mapper')->map($paymentAccountType);
        $depositAccount = $group->getDepositAccountForCurrentPaymentProcessor($depositAccountType);

        /** @var SubmerchantProcessorInterface $paymentProcessor */
        $paymentProcessor = $this->get('payment_processor.factory')->getPaymentProcessor($group);
        $paymentProcessor->registerPaymentAccount($paymentAccountMapped, $depositAccount);

        return $paymentAccountMapped->getEntity();
    }

    /**
     * Creates a new billing account, so a landlord can pay RentTrack.
     *
     * @param  Form     $billingAccountType
     * @param  Landlord $user
     * @param  Group    $group
     * @return mixed
     */
    protected function createBillingAccount(Form $billingAccountType, Landlord $user, Group $group)
    {
        /** @var BillingAccount $billingAccount */
        $billingAccount = $billingAccountType->getData();
        $billingAccount->setGroup($group);

        // call out to PaymentProcessor interface for RentTrack payment token
        $mapper = $this->get('payment_account.type.mapper');
        $paymentAccountMapped = $mapper->mapLandlordAccountTypeForm($billingAccountType);
        /** @var SubmerchantProcessorInterface $paymentProcessor */
        $paymentProcessor = $this->get('payment_processor.factory')->getPaymentProcessor($group);
        // We can use any contract because we use only it just for get group in this case
        $paymentProcessor->registerBillingAccount($paymentAccountMapped, $user);

        return $billingAccount;
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

        // TODO: set deposit account from form when you implement PayAnything story
        $depositAccount = $contract->getGroup()->getRentDepositAccountForCurrentPaymentProcessor();
        if (null !== $depositAccount) {
            $paymentEntity->setDepositAccount($depositAccount);
        } else {
            throw $this->createNotFoundException('DepositAccount cannot be null');
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
