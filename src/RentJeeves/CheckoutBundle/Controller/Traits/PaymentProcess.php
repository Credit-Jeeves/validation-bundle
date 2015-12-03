<?php
namespace RentJeeves\CheckoutBundle\Controller\Traits;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\CheckoutBundle\PaymentProcessor\SubmerchantProcessorInterface;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\BillingAccount;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
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
     * @param Group $group
     * @param Tenant $tenant
     * @param string $depositAccountType
     * @return mixed
     */
    protected function savePaymentAccount(
        Form $paymentAccountType,
        Group $group,
        Tenant $tenant,
        $depositAccountType = DepositAccountType::RENT
    ) {
        /** @var \RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount $paymentAccountMapped */
        $paymentAccountMapped = $this->get('payment_account.type.mapper')->map($paymentAccountType);
        $paymentAccountMapped->getEntity()->setUser($tenant);

        if ($paymentAccountMapped->getEntity()->getType() === PaymentAccountType::DEBIT_CARD) {
            if (!$this->get('binlist.card')->isLowDebitFee($paymentAccountMapped->get('card_number'))) {
                throw new \InvalidArgumentException(
                    $this->get('translator')->trans('checkout.error.type.debit_card.invalid')
                );
            }

            $paymentAccountMapped->getEntity()->setLastFour(substr($paymentAccountMapped->get('card_number'), -4));
            $paymentAccountMapped->getEntity()->setRegistered(true);
        }

        $depositAccount = $group->getDepositAccountForCurrentPaymentProcessor($depositAccountType);

        /** @var SubmerchantProcessorInterface $paymentProcessor */
        $paymentProcessor = $this->get('payment_processor.factory')->getPaymentProcessor($group);
        $paymentProcessor->registerPaymentAccount($paymentAccountMapped, $depositAccount);

        return $paymentAccountMapped->getEntity();
    }

    /**
     * @param Form $paymentAccountType
     * @return PaymentAccount
     */
    protected function updatePaymentAccount(Form $paymentAccountType)
    {
        /** @var PaymentAccount $paymentAccount */
        $paymentAccount = $paymentAccountType->getData();

        /** @var SubmerchantProcessorInterface $paymentProcessor */
        $paymentProcessor = $this
            ->get('payment_processor.factory')
            ->getPaymentProcessorByPaymentAccount($paymentAccount);

        /** @var \RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount $paymentAccountMapped */
        $paymentAccountMapped = $this->get('payment_account.type.mapper')->map($paymentAccountType);

        $paymentProcessor->modifyPaymentAccount($paymentAccountMapped);

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
        /** @var EntityManager $em */
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

        $depositAccount = $contract->getGroup()->getRentDepositAccountForCurrentPaymentProcessor();
        if (null !== $depositAccount) {
            $paymentEntity->setDepositAccount($depositAccount);
        } else {
            throw $this->createNotFoundException('DepositAccount cannot be null');
        }

        if ($pidkiqEnabled && !$this->isVerifiedUser($request, $contract)) {
            throw $this->createNotFoundException('User verification failed');
        }

        if (null == $paymentEntity->getId()) { // if new payment comes
            /** Prevent creating duplicated rent payment for one contract*/
            $existingPayment = $em->getRepository('RjDataBundle:Payment')->findActiveRentPaymentForContract($contract);
            if (null !== $existingPayment) {
                throw new \Exception($this->get('translator')->trans(
                    'checkout.duplicate_payment.error',
                    ['%support_email%' => $this->container->getParameter('support_email')]
                ));
            }
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

        $this->get('payment.amount_limit')->checkIfExceedsMax($paymentEntity);

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
