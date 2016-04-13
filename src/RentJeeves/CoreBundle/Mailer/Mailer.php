<?php
namespace RentJeeves\CoreBundle\Mailer;

use CreditJeeves\CoreBundle\Mailer\Mailer as BaseMailer;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\OrderPayDirect;
use CreditJeeves\DataBundle\Entity\User;
use RentJeeves\CheckoutBundle\Payment\BusinessDaysCalculator;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorAciPayAnyone;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\TrustedLandlord;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;

class Mailer extends BaseMailer
{
    /**
     * @param User $user
     *
     * @return bool
     */
    public function sendRjCheckEmail(User $user)
    {
        $url = $this->container->get('router')->generate('tenant_new_check', ['code' => $user->getInviteCode()], true);

        return $this->sendEmail($user, 'rjCheck', ['checkUrl' => $url]);
    }

    /**
     * @param Landlord $landlord
     * @param Tenant $tenant
     * @param Contract $contract
     *
     * @return bool
     */
    public function sendRjLandLordInvite(Landlord $landlord, Tenant $tenant, Contract $contract)
    {
        $vars = [
            'nameLandlord' => $landlord->getFirstName(),
            'fullNameTenant' => $tenant->getFullName(),
            'nameTenant' => $tenant->getFirstName(),
            'address' => $contract->getProperty() ? $contract->getProperty()->getAddress() : null,
            'unitName' => $contract->getUnit() ? $contract->getUnit()->getName() : $contract->getSearch(),
            'inviteCode' => $landlord->getInviteCode(),
        ];

        return $this->sendBaseLetter('rjLandLordInvite', $vars, $landlord);
    }

    /**
     * @param Tenant $tenant
     * @param Landlord $landlord
     * @param Contract $contract
     * @param string $isImported
     *
     * @return bool
     */
    public function sendRjTenantInvite(Tenant $tenant, Landlord $landlord, Contract $contract, $isImported = null)
    {
        $vars = [
            'fullNameLandlord' => $landlord->getFullName(),
            'groupName' => $contract->getGroup()->getName(),
            'holdingName' => $contract->getGroup()->getHolding()->getName(),
            'nameTenant' => $tenant->getFirstName(),
            'address' => $contract->getProperty()->getAddress(),
            'rentAddress' => $contract->getRentAddress(),
            'unitName' => $contract->getUnit() ? $contract->getUnit()->getName() : '',
            'inviteCode' => $tenant->getInviteCode(),
            'isImported' => $isImported,
        ];

        return $this->sendBaseLetter('rjTenantInvite', $vars, $tenant);
    }

    /**
     * @param Tenant $tenant
     * @param Landlord $landlord
     * @param Contract $contract
     *
     * @return bool
     */
    public function sendRjTenantLatePayment(Tenant $tenant, Landlord $landlord, Contract $contract)
    {
        $vars = [
            'groupName' => $contract->getGroup()->getName(),
            'holdingName' => $contract->getGroup()->getHolding()->getName(),
            'fullNameLandlord' => $landlord->getFullName(),
            'nameTenant' => $tenant->getFirstName(),
            'address' => $contract->getProperty()->getAddress(),
            'unitName' => $contract->getUnit()->getName(),
            'inviteCode' => $tenant->getInviteCode(),
        ];

        return $this->sendBaseLetter('rjTenantLatePayment', $vars, $tenant);
    }

    /**
     * @param Tenant $tenant
     * @param Landlord $landlord
     * @param Contract $contract
     *
     * @return bool
     */
    public function sendRjLandlordComeFromInvite(Tenant $tenant, Landlord $landlord, Contract $contract)
    {
        $unitName = $contract->getUnit() ? $contract->getUnit()->getName() : $contract->getSearch();
        $vars = [
            'nameTenant' => $tenant->getFirstName(),
            'fullNameLandlord' => $landlord->getFullName(),
            'address' => $contract->getProperty()->getAddress(),
            'unitName' => $unitName,
            'rentAmount' => $contract->getRent(),
        ];

        return $this->sendBaseLetter('rjLandlordComeFromInvite', $vars, $tenant);
    }

    /**
     * @param Contract $contract
     * @param string $paymentType
     * @param boolean $isRecurringPaymentEnded
     * @param float $paymentTotal
     *
     * @return bool
     */
    public function sendRjPaymentDue(
        Contract $contract,
        $paymentType = null,
        $isRecurringPaymentEnded = true,
        $paymentTotal = null
    ) {
        $vars = [
            'nameHolding' => $contract->getHolding()->getName(),
            'nameTenant' => $contract->getTenant()->getFullName(),
            'address' => $contract->getRentAddress($contract->getProperty(), $contract->getUnit()),
            'paymentType' => $paymentType,
            'isRecurringPaymentEnded' => $isRecurringPaymentEnded,
            'paymentTotal' => $paymentTotal
        ];

        return $this->sendBaseLetter(
            'rjPaymentDue',
            $vars,
            $contract->getTenant()
        );
    }

    /**
     * @param Landlord $landlord
     * @param array $failureBatchDetails
     * @param string $filePath
     *
     * @return bool
     */
    public function sendPostPaymentError(Landlord $landlord, array $failureBatchDetails, $filePath)
    {
        return $this->sendBaseLetter(
            'rjPostPaymentError',
            [
                'landlordName' => $landlord->getFullName(),
                'details' => $failureBatchDetails,
            ],
            $landlord,
            $filePath
        );
    }

    /**
     * @param Landlord $landlord
     * @param Contract $contract
     *
     * @return bool
     */
    public function sendPendingContractToLandlord(Landlord $landlord, Contract $contract)
    {
        $vars = [
            'nameLandlord' => $landlord->getFullName(),
            'nameTenant' => $contract->getTenant()->getFullName(),
            'address' => $contract->getRentAddress(),
        ];

        return $this->sendBaseLetter('rjPendingContract', $vars, $landlord);
    }

    /**
     * @param Landlord $landlord
     * @param float    $amount
     * @param string   $sTemplate
     *
     * @return bool
     */
    public function sendTodayPayments(Landlord $landlord, $amount, $sTemplate = 'rjTodayPayments')
    {
        $vars = [
            'nameLandlord' => $landlord->getFullName(),
            'amount' => $amount,
        ];

        return $this->sendBaseLetter($sTemplate, $vars, $landlord);
    }

    /**
     * @param Landlord $landlord
     * @param          $report
     *
     * @return bool
     */
    public function sendRjDailyReport(Landlord $landlord, $report)
    {
        $vars = [
            'nameLandlord' => $landlord->getFullName(),
            'report' => $report,
        ];

        return $this->sendBaseLetter('rjDailyReport', $vars, $landlord);
    }

    /**
     * @param Tenant   $tenant
     * @param Contract $contract
     * @param string   $diff
     *
     * @return bool
     */
    public function sendRjTenantLateContract(Tenant $tenant, Contract $contract, $diff)
    {
        $vars = [
            'nameTenant' => $tenant->getFullName(),
            'diff' => $diff,
            'address' => $contract->getRentAddress($contract->getProperty(), $contract->getUnit()),
        ];

        return $this->sendBaseLetter('rjTenantLateContract', $vars, $tenant);
    }

    /**
     * @param Landlord $landlord
     * @param array    $tenants
     *
     * @return bool
     */
    public function sendListLateContracts(Landlord $landlord, array $tenants)
    {
        $vars = [
            'nameLandlord' => $landlord->getFullName(),
            'tenants' => $tenants,
        ];

        return $this->sendBaseLetter('rjListLateContracts', $vars, $landlord);
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    public function sendPaymentReceipt(Order $order)
    {
        $tenant = $order->getUser();
        $history = $order->getCompleteTransaction();
        $fee = $order->getFee();
        $amount = $order->getSum();
        $total = $fee + $amount;

        $vars = [
            'nameTenant' => $tenant->getFullName(),
            'datetime' => $order->getUpdatedAt()->format('Y-m-d'),
            'transactionID' => $history ? $history->getTransactionId() : 'N/A',
            'amount' => $amount,
            'fee' => $fee,
            'total' => $total,
            'groupName' => $order->getGroupName(),
            'rentAmount' => $order->getRentAmount(),
            'otherAmount' => $order->getOtherAmount(),
            'paymentProcessor' => $order->getPaymentProcessor(),
            'type' => $order->getPaymentType(),
            'depositType' => $order->getDepositAccount()->getTitleName(),
            'statementDescriptor' => $this->getStatementDescriptor($order),
            'paymentType' => $order->getPayment() ? $order->getPayment()->getType() : null,
            'paymentCreatedAt' => $order->getPayment() ? $order->getPayment()->getCreatedAt()->format('Y-m-d') : null,
            'lastFour' => $order->getPaymentAccount() ? $order->getPaymentAccount()->getLastFour() : '',
        ];

        return $this->sendBaseLetter('rjOrderReceipt', $vars, $tenant);
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    public function sendRentError(Order $order)
    {
        $tenant = $order->getUser();
        $fee = $order->getFee();
        $amount = $order->getSum();
        $total = $fee + $amount;
        $vars = [
            'nameTenant' => $tenant->getFullName(),
            'datetime' => $order->getUpdatedAt()->format('m/d/Y H:i:s'),
            'amount' => $amount,
            'fee' => $fee,
            'total' => $total,
            'groupName' => $order->getGroupName(),
            'orderId' => $order->getId(),
            'error' => $order->getErrorMessage(),
            'transactionId' => $order->getTransactionId(),
            'rentAmount' => $order->getRentAmount(),
            'otherAmount' => $order->getOtherAmount(),
            'orderType' => $order->getPaymentType(),
        ];

        return $this->sendBaseLetter('rjOrderError', $vars, $tenant);
    }

    /**
     * @param Order $order
     *
     * @return boolean
     */
    public function sendScoreTrackError(Order $order)
    {
        $vars = [
            'nameTenant' => $order->getUser()->getFullName(),
            'date' => $order->getUpdatedAt()->format('m/d/Y'),
            'amount' => $this->container->getParameter('credittrack_payment_per_month_currency').$order->getSum(),
            'number' => $order->getTransactionId(),
            'error' => $order->getErrorMessage(),
        ];

        return $this->sendBaseLetter(
            'rjScoreTrackOrderError',
            $vars,
            $order->getUser()
        );
    }

    /**
     * @param Tenant   $tenant
     * @param Landlord $landlord
     * @param Contract $contract
     *
     * @return bool
     */
    public function sendRjTenantInviteReminder(Tenant $tenant, Landlord $landlord, Contract $contract)
    {
        $vars = [
            'fullNameLandlord' => $landlord->getFullName(),
            'groupName' => $contract->getGroup()->getName(),
            'holdingName' => $contract->getGroup()->getHolding()->getName(),
            'nameTenant' => $tenant->getFirstName(),
            'address' => $contract->getProperty()->getAddress(),
            'unitName' => $contract->getUnit() ? $contract->getUnit()->getName() : '',
            'inviteCode' => $tenant->getInviteCode(),
        ];

        return $this->sendBaseLetter('rjTenantInviteReminder', $vars, $tenant);
    }

    /**
     * @param Contract $contract
     *
     * @return bool
     */
    public function sendContractApprovedToTenant(Contract $contract)
    {
        $tenant = $contract->getTenant();
        $vars = ['nameTenant' => $tenant->getFullName()];

        return $this->sendBaseLetter('rjContractApproved', $vars, $tenant);
    }

    /**
     * @param Tenant   $tenant
     * @param Landlord $landlord
     * @param Contract $contract
     *
     * @return bool
     */
    public function sendRjContractRemovedFromDbByLandlord(Tenant $tenant, Landlord $landlord, Contract $contract)
    {
        $vars = [
            'fullNameLandlord' => $landlord->getFullName(),
            'fullNameTenant' => $tenant->getFullName(),
            'address' => $contract->getProperty()->getAddress(),
            'unitName' => $contract->getUnit() ? $contract->getUnit()->getName() : '',
        ];

        return $this->sendBaseLetter(
            'rjContractRemovedFromDbByLandlord',
            $vars,
            $tenant
        );
    }

    /**
     * @param Tenant   $tenant
     * @param Landlord $landlord
     * @param Contract $contract
     *
     * @return bool
     */
    public function sendRjContractRemovedFromDbByTenant(Tenant $tenant, Landlord $landlord, Contract $contract)
    {
        $vars = [
            'fullNameLandlord' => $landlord->getFullName(),
            'fullNameTenant' => $tenant->getFullName(),
            'address' => $contract->getProperty()->getAddress(),
            'unitName' => $contract->getUnit() ? $contract->getUnit()->getName() : '',
        ];

        return $this->sendBaseLetter(
            'rjContractRemovedFromDbByTenant',
            $vars,
            $landlord
        );
    }

    /**
     * @param Contract $contract
     * @param Landlord $landlord
     * @param Tenant   $tenant
     *
     * @return bool
     */
    public function endContractByLandlord(Contract $contract, Landlord $landlord, Tenant $tenant)
    {
        // Unit is a Doctrine Proxy, it always exists, but it throws an exception when we try to get unit's name
        try {
            $unitName = $contract->getUnit()->getName();
        } catch (\Exception $e) {
            $unitName = '';
        }
        $vars = [
            'tenantFullName' => $tenant->getFullName(),
            'landlordFullName' => $landlord->getFullName(),
            'uncollectedBalance' => $contract->getUncollectedBalance(),
            'address' => $contract->getProperty()->getAddress(),
            'unitName' => $unitName,
        ];

        return $this->sendBaseLetter('rjEndContract', $vars, $tenant);
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    public function sendOrderCancelToTenant(Order $order)
    {
        $tenant = $order->getContract()->getTenant();

        $vars = [
            'tenantFullName' => $tenant->getFullName(),
            'orderStatus' => $order->getStatus(),
            'rentAmount' => $order->getSum(),
            'orderDate' => $order->getUpdatedAt()->format('m/d/Y H:i:s'),
            'reversalDescription' => $order->getReversalDescription(),
        ];

        return $this->sendBaseLetter('rjOrderCancel', $vars, $tenant);
    }

    /**
     * @param Order $order
     */
    public function sendOrderCancelToLandlord(Order $order)
    {
        $tenant = $order->getContract()->getTenant();
        $vars = [
            'landlordFirstName' => '',
            'orderStatus' => $order->getStatus(),
            'rentAmount' => $order->getSum(),
            'orderDate' => $order->getUpdatedAt()->format('m/d/Y H:i:s'),
            'tenantName' => $tenant->getFullName(),
            'reversalDescription' => $order->getReversalDescription(),
        ];

        $group = $order->getContract()->getGroup();
        /** @var Landlord $landlord */
        foreach ($group->getGroupAgents() as $landlord) {
            $vars['landlordFirstName'] = $landlord->getFirstName();
            $this->sendBaseLetter('rjOrderCancelToLandlord', $vars, $landlord);
        }
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    public function sendPendingInfo(Order $order)
    {
        $tenant = $order->getContract()->getTenant();
        $transaction = $order->getCompleteTransaction();
        $amount = $order->getSum();
        $fee = $order->getFee();
        $total = $fee + $amount;
        $vars = [
            'tenantName' => $tenant->getFullName(),
            'orderTime' => $order->getUpdatedAt()->format('Y-m-d'),
            'transactionID' => $transaction ? $transaction->getTransactionId() : 'N/A',
            'amount' => $amount,
            'fee' => $fee,
            'total' => $total,
            'groupName' => $order->getGroupName(),
            'rentAmount' => $order->getRentAmount(),
            'otherAmount' => $order->getOtherAmount(),
            'paymentProcessor' => $order->getPaymentProcessor(),
            'type' => $order->getPaymentType(),
            'statementDescriptor' => $this->getStatementDescriptor($order),
            'paymentType' => $order->getPayment() ? $order->getPayment()->getType() : null,
            'paymentCreatedAt' => $order->getPayment() ? $order->getPayment()->getCreatedAt()->format('Y-m-d') : null,
            'lastFour' => $order->getPaymentAccount() ? $order->getPaymentAccount()->getLastFour() : '',
        ];

        return $this->sendBaseLetter('rjPendingOrder', $vars, $tenant);
    }

    /**
     * @param Contract $contract
     * @param Payment  $payment
     *
     * @return bool
     */
    public function sendContractAmountChanged(Contract $contract, Payment $payment)
    {
        $tenant = $contract->getTenant();
        $vars = [
            'tenantName' => $tenant->getFullName(),
            'rentAmount' => $contract->getRent(),
            'paymentAmount' => $payment->getAmount(),
            'groupName' => $contract->getGroup()->getName(),
            'holdingName' => $contract->getGroup()->getHolding()
        ];

        return $this->sendBaseLetter('rjContractAmountChanged', $vars, $tenant);
    }

    /**
     * @param Landlord  $landlord
     * @param array     $groups
     * @param \DateTime $date
     * @param string    $resend
     *
     * @return bool
     */
    public function sendBatchDepositReportHolding(Landlord $landlord, $groups, \DateTime $date, $resend = null)
    {
        $currentAccountingSystem = $landlord->getHolding()->getAccountingSystem();
        $vars = [
            'landlordFirstName' => $landlord->getFirstName(),
            'date' => $date,
            'groups' => $groups,
            'resend' => $resend,
            'info' => $this->getInfoForAccountingSystem($currentAccountingSystem),
        ];

        return $this->sendBaseLetter(
            'rjBatchDepositReportHolding',
            $vars,
            $landlord
        );
    }

    /**
     * @param Landlord  $landlord
     * @param Group     $group
     * @param \DateTime $date
     * @param array     $batches
     * @param           $returns
     * @param           $resend
     *
     * @return bool
     */
    public function sendBatchDepositReportLandlord(
        Landlord $landlord,
        Group $group,
        \DateTime $date,
        $batches,
        $returns,
        $resend = null
    ) {
        $currentAccountingSystem = $landlord->getHolding()->getAccountingSystem();
        $vars = [
            'landlordFirstName' => $landlord->getFirstName(),
            'date' => $date,
            'groupName' => $group->getName(),
            'groupPaymentProcessor' => $group->getGroupSettings()->getPaymentProcessor(),
            'batches' => $batches,
            'returns' => $returns,
            'resend' => $resend,
            'info' => $this->getInfoForAccountingSystem($currentAccountingSystem),
        ];

        return $this->sendBaseLetter(
            'rjBatchDepositReportLandlord',
            $vars,
            $landlord
        );
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    public function sendReportReceipt(Order $order)
    {
        $dateShortFormat = $this->container->getParameter('date_short');
        $amout = sprintf(
            '%s%s',
            $this->container->getParameter('credittrack_payment_per_month_currency'),
            number_format($order->getSum(), 2, '.', '')
        );

        return $this->sendEmail(
            $order->getUser(),
            'rjReceipt',
            [
                'tenantName' => $order->getUser()->getFullName(),
                'date' => $order->getCreatedAt()->format($dateShortFormat),
                'amout' => $amout,
                'number' => $order->getTransactionId(),
                'paymentProcessor' => $order->getPaymentProcessor(),
                'type' => $order->getPaymentType(),
                'statementDescriptor' => $this->getStatementDescriptor($order),
            ]
        );
    }

    /**
     * @return bool
     */
    public function sendFreeReportUpdated(Tenant $tenant)
    {
        return $this->sendEmail(
            $tenant,
            'rjFreeReportUpdated',
            [
                'tenantFirstName' => $tenant->getFirstName(),
                'dashboardLink' => $this->container->get('router')->generate('tenant_summary', [], true)
            ]
        );
    }

    /**
     * @param Landlord $landlord
     * @param array    $data
     *
     * @return bool
     */
    public function sendPushBatchReceiptsReport(Landlord $landlord, array $data)
    {
        return $this->sendBaseLetter(
            $template = 'rjPushBatchReceiptsReport',
            ['data' => $data],
            $landlord
        );
    }

    /**
     * @param Tenant $tenant
     *
     * @return bool
     */
    public function sendEmailAcceptPayment(Tenant $tenant)
    {
        $context = $this->container->get('router')->getContext();
        $context->setHost($this->container->getParameter('server_name_rj'));

        $url = $this->container->get('router')->generate(
            'fos_user_security_login',
            [],
            true
        );

        return $this->sendBaseLetter(
            $template = 'rjYardiPaymentAcceptedTurnOn',
            [
                'TenantName' => $tenant->getFullName(),
                'href' => $url,
            ],
            $tenant
        );
    }

    /**
     * @param Tenant $tenant
     *
     * @return bool
     */
    public function sendEmailDoNotAcceptPayment(Tenant $tenant)
    {
        return $this->sendBaseLetter(
            $template = 'rjYardiPaymentAcceptedTurnOff',
            ['TenantName' => $tenant->getFullName()],
            $tenant
        );
    }

    /**
     * @param Contract $contract
     *
     * @return bool
     */
    public function sendEmailPaymentFlaggedByUntrustedLandlordRule(Contract $contract)
    {
        $tenant = $contract->getTenant();
        $jiraTicket = null;
        if ($trustedLandlord = $contract->getGroup()->getTrustedLandlord() and $trustedLandlord->getJiraMapping()) {
            $jiraTicket = $trustedLandlord->getJiraMapping()->getJiraKey();
        }

        return $this->sendBaseLetter(
            $template = 'rjPaymentFlaggedByUntrustedLandlordRule',
            [
                'firstName' => $tenant->getFirstName(),
                'propertyAddress' => $contract->getTenantRentAddress(),
                'jiraTicket' => $jiraTicket
            ],
            $tenant,
            null,
            false
        );
    }

    /**
     * @param Landlord   $landlord
     * @param Contract[] $contracts
     * @param string     $month
     *
     * @return bool
     */
    public function sendLateReportingReviewEmailToLandlord(Landlord $landlord, array $contracts, $month)
    {
        return $this->sendBaseLetter(
            $template = 'rjLateReportingLandlord',
            [
                'landlordName' => $landlord->getFullName(),
                'contracts' => $contracts,
                'month' => $month,
            ],
            $landlord
        );
    }

    /**
     * @param Tenant $tenant
     * @param string $month
     *
     * @return bool
     */
    public function sendLateReportingReviewEmailToTenant(Tenant $tenant, $month)
    {
        return $this->sendBaseLetter(
            $template = 'rjLateReportingTenant',
            [
                'tenantName' => $tenant->getFullName(),
                'month' => $month,
            ],
            $tenant
        );
    }

    /**
     * @param OrderPayDirect $order
     *
     * @return bool
     */
    public function sendOrderSendingNotification(OrderPayDirect $order)
    {
        $tenant = $order->getUser();
        $group = $order->getContract()->getGroup();
        $mailingAddress = sprintf(
            '%s, %s, %s, %s',
            $group->getStreetAddress1(),
            $group->getCity(),
            $group->getState(),
            $group->getZip()
        );

        $estimatedDeliveryDate = BusinessDaysCalculator::getBusinessDate(
            $order->getCreatedAt(),
            PaymentProcessorAciPayAnyone::DELIVERY_BUSINESS_DAYS_FOR_BANK
        );

        $vars = [
            'firstName' => $tenant->getFirstName(),
            'groupName' => $order->getGroupName(),
            'estimatedDelivery' => $estimatedDeliveryDate->format('m/d/Y'),
            'sendDate' => $order->getDepositOutboundTransaction()->getCreatedAt()->format('m/d/Y'),
            'checkAmount' => $order->getDepositOutboundTransaction()->getAmount(),
            'mailingAddress' => $mailingAddress,
            'mailingAddressName' => $group->getMailingAddressName(),
        ];

        return $this->sendBaseLetter('rjOrderSending', $vars, $tenant);
    }

    /**
     * @param OrderPayDirect $order
     * @return bool
     */
    public function sendOrderPayDirectCompleteNotification(OrderPayDirect $order)
    {
        $tenant = $order->getUser();
        $vars = [
            'firstName' => $tenant->getFirstName(),
            'groupName' => $order->getGroupName(),
            'amount' => $order->getSum(),
            'date' => $order->getUpdatedAt()->format('m/d/Y'),
        ];

        return $this->sendBaseLetter('rjOrderPayDirectComplete', $vars, $tenant);
    }

    /**
     * @param OrderPayDirect $order
     *
     * @return bool
     */
    public function sendOrderRefundingNotification(OrderPayDirect $order)
    {
        $tenant = $order->getUser();

        $vars = [
            'firstName' => $tenant->getFirstName(),
            'totalAmount' => $order->getFee() + $order->getSum(),
            'rentalAddress' => $order->getContract()->getRentAddress(),
            'paymentAcctName' => $order->getPaymentAccount() ? $order->getPaymentAccount()->getName() : '',
        ];

        return $this->sendBaseLetter('rjOrderRefunding', $vars, $tenant);
    }

    /**
     * @param OrderPayDirect $order
     *
     * @return bool
     */
    public function sendOrderReissuedNotification(OrderPayDirect $order)
    {
        $tenant = $order->getUser();

        $vars = [
            'firstName' => $tenant->getFirstName(),
            'totalAmount' => $order->getFee() + $order->getSum(),
            'rentalAddress' => $order->getContract()->getRentAddress(),
        ];

        return $this->sendBaseLetter('rjOrderReissued', $vars, $tenant);
    }

    /**
     * @param Order $order
     * @return string
     */
    protected function getStatementDescriptor(Order $order)
    {
        $statementDescriptorPrefix = $order->getPaymentProcessor() == PaymentProcessor::HEARTLAND ? 'RENTTRK' : 'ORC';
        $statementDescriptor = $order->getContract() ? $order->getContract()->getGroup()->getStatementDescriptor() : '';

        return sprintf('%s*%s', $statementDescriptorPrefix, $statementDescriptor);
    }

    /**
     * @param Contract $contract
     *
     * @return bool
     */
    public function sendSecondChanceForContract(Contract $contract)
    {
        $params = [
            'FNAME' => $contract->getTenant()->getFirstName(),
            'LANDLORDGR' => $contract->getGroup()->getName(),
            'INVITECODE' => $contract->getTenant()->getInviteCode(),
            'FEEACH' => $contract->getGroupSettings()->getFeeACH(),
            'FEECC' => $contract->getGroupSettings()->getFeeCC(),
        ];
        if (null !== $contract->getFinishAt()) {
            $date = new \DateTime();
            $interval = date_diff($date, $contract->getFinishAt());
            $params['MONTHSLEFT'] = $interval->format('%y') * 12 + $interval->format('%m');
        } else {
            $params['MONTHSLEFT'] = 0;
        }

        return $this->sendBaseLetter(
            $template = 'rjSecondChanceForContract',
            $params,
            $contract->getTenant()
        );
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    public function sendChurnRecaptureForOrder(Order $order)
    {
        $isReporting = false;
        if ($order->getContract()->getReportToExperian() || $order->getContract()->getReportToTransUnion()) {
            $isReporting = true;
        }

        $surveyUrl = $this->container->getParameter('mailer.survey_url');
        $contract = $order->getContract();
        $leaseEnd = $contract->getFinishAt() === null ? false : $contract->getFinishAt()->format('Y-m-d');

        $params = [
            'FNAME' => $order->getUser()->getFirstName(),
            'LAST_PAYMENT_DATE' => $order->getCreatedAt()->format('Y-m-d'),
            'LAST_PAYMENT_AMOUNT' => $order->getSum(),
            'LEASE_END' => $leaseEnd,
            'REPORTING' => $isReporting,
            'SURVEY_URL' => $surveyUrl,
        ];

        return $this->sendBaseLetter(
            $template = 'rjChurnRecapture',
            $params,
            $order->getUser()
        );
    }

    /**
     * @param Payment $payment
     * @return bool
     */
    public function sendTrustedLandlordDenied(Payment $payment)
    {
        $tenant = $payment->getContract()->getTenant();
        $params = [
            'tenantFirstName' => $tenant->getFirstName(),
        ];

        return $this->sendBaseLetter(
            $template = 'rjTrustedLandlordDenied',
            $params,
            $tenant
        );
    }

    /**
     * @param Payment $payment
     * @return bool
     */
    public function sendTrustedLandlordApproved(Payment $payment)
    {
        $tenant = $payment->getContract()->getTenant();
        $trustedLandlord = $payment->getContract()->getGroup()->getTrustedLandlord();
        $params = [
            'tenantFirstName' => $tenant->getFirstName(),
            'trustedLandlordFullName' => $trustedLandlord->getFullName(),
            'trustedLandlordAddress' => $trustedLandlord->getCheckMailingAddress()->getFullAddress()
        ];

        return $this->sendBaseLetter(
            $template = 'rjTrustedLandlordApproved',
            $params,
            $tenant
        );
    }

    /**
     * Use for sendBatchDepositReportLandlord and sendBatchDepositReportLandlord
     *
     * @var string $accountingSystem
     *
     * @return array|null
     */
    protected function getInfoForAccountingSystem($accountingSystem)
    {
        switch ($accountingSystem) {
            case AccountingSystem::YARDI_VOYAGER:
                return [
                    'title' => 'How to Post Your Batch in Yardi',
                    'link' => 'http://help.renttrack.com/knowledgebase/' .
                        'articles/647266-how-does-the-yardi-integration-work',
                ];
                break;
            case AccountingSystem::YARDI_GENESIS:
            case AccountingSystem::YARDI_GENESIS_2:
                return [
                    'title' => 'How do I export payments for Yardi Genesis and Yardi Genesis V2',
                    'link' => 'http://help.renttrack.com/knowledgebase/' .
                        'articles/436072-how-do-i-export-payments-for-yardi-genesis-and-yar',
                ];
                break;
            case AccountingSystem::MRI:
                return [
                    'title' => 'How to Post Your Batch in MRI',
                    'link' => 'http://help.renttrack.com/knowledgebase' .
                        '/articles/559914-how-does-the-mri-integration-work',
                ];
                break;
            case AccountingSystem::PROMAS:
                return [
                    'title' => 'How to export payments for ProMas',
                    'link' => 'http://help.renttrack.com/knowledgebase/' .
                        'articles/389562-how-do-i-export-payments-for-promas',
                ];
                break;
            default:
                return null;
                break;
        }
    }
}
