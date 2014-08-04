<?php
namespace RentJeeves\CoreBundle\Mailer;

use CreditJeeves\CoreBundle\Mailer\Mailer as BaseMailer;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Order;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Contract;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Exception;
use RuntimeException;
use DateTime;
use CreditJeeves\DataBundle\Enum\OrderType;

/**
 * 
 */
class Mailer extends BaseMailer
{
    public function sendRjCheckEmail($user, $sTemplate = 'rjCheck')
    {
        $url = $this->container->get('router')->generate(
            'tenant_new_check',
            array('code' => $user->getInviteCode()),
            true
        );
        
        return $this->sendEmail(
            $user,
            $sTemplate,
            array(
               'checkUrl' => $url
            )
        );
    }

    public function sendRjLandLordInvite($landlord, $tenant, $contract, $sTemplate = 'rjLandLordInvite')
    {
        $vars = array(
            'nameLandlord'          => $landlord->getFirstName(),
            'fullNameTenant'        => $tenant->getFullName(),
            'nameTenant'            => $tenant->getFirstName(),
            'address'               => $contract->getProperty()->getAddress(),
            'unitName'              => ($contract->getUnit())? $contract->getUnit()->getName() : null,
            'inviteCode'            => $landlord->getInviteCode(),
        );

        return $this->sendBaseLetter($sTemplate, $vars, $landlord->getEmail(), $landlord->getCulture());
    }

    public function sendRjTenantInvite($tenant, $landlord, $contract, $isImported = null, $sTemplate = 'rjTenantInvite')
    {
        $unit = $contract->getUnit();
        $vars = array(
            'fullNameLandlord'      => $landlord->getFullName(),
            'nameTenant'            => $tenant->getFirstName(),
            'address'               => $contract->getProperty()->getAddress(),
            'rentAddress'           => $contract->getRentAddress(),
            'unitName'              => $unit ? $unit->getName() : '',
            'inviteCode'            => $tenant->getInviteCode(),
            'isImported'            => $isImported,
        );

        return $this->sendBaseLetter($sTemplate, $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    public function sendRjTenantLatePayment($tenant, $landlord, $contract, $sTemplate = 'rjTenantLatePayment')
    {
        $vars = array(
            'fullNameLandlord'      => $landlord->getFullName(),
            'nameTenant'            => $tenant->getFirstName(),
            'address'               => $contract->getProperty()->getAddress(),
            'unitName'              => $contract->getUnit()->getName(),
            'inviteCode'            => $tenant->getInviteCode(),
        );

        return $this->sendBaseLetter($sTemplate, $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    public function sendRjLandlordComeFromInvite($tenant, $landlord, $contract, $sTemplate = 'rjLandlordComeFromInvite')
    {
        $unitName = $contract->getUnit()? $contract->getUnit()->getName() : $contract->getSearch();
        $vars = array(
            'nameTenant'            => $tenant->getFirstName(),
            'fullNameLandlord'      => $landlord->getFullName(),
            'address'               => $contract->getProperty()->getAddress(),
            'unitName'              => $unitName,
            'rentAmount'            => $contract->getRent(),
        );

        return $this->sendBaseLetter($sTemplate, $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    public function sendRjPaymentDue($tenant, $holding, $contract, $recurring = false, $sTemplate = 'rjPaymentDue')
    {
        $vars = array(
            'nameHolding' => $holding->getName(),
            'nameTenant' => $tenant->getFullName(),
            'address' => $contract->getRentAddress($contract->getProperty(), $contract->getUnit()),
            'recurring' => $recurring,
        );
        return $this->sendBaseLetter($sTemplate, $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    public function sendPendingContractToLandlord($landlord, $tenant, $contract, $sTemplate = 'rjPendingContract')
    {
        $vars = array(
            'nameLandlord' => $landlord->getFullName(),
            'nameTenant' => $tenant->getFullName(),
            'address' => $contract->getRentAddress($contract->getProperty(), $contract->getUnit()),
        );
        return $this->sendBaseLetter($sTemplate, $vars, $landlord->getEmail(), $landlord->getCulture());
    }

    public function sendTodayPayments($landlord, $amount, $sTemplate = 'rjTodayPayments')
    {
        $vars = array(
            'nameLandlord' => $landlord->getFullName(),
            'amount' => $amount,
        );
        return $this->sendBaseLetter($sTemplate, $vars, $landlord->getEmail(), $landlord->getCulture());
    }

    public function sendRjDailyReport($landlord, $report, $sTemplate = 'rjDailyReport')
    {
        $vars = array(
            'nameLandlord' => $landlord->getFullName(),
            'report' => $report,
        );
        return $this->sendBaseLetter($sTemplate, $vars, $landlord->getEmail(), $landlord->getCulture());
    }

    public function sendRjTenantLateContract($tenant, $contract, $diff, $sTemplate = 'rjTenantLateContract')
    {
        $vars = array(
                'nameTenant' => $tenant->getFullName(),
                'diff' => $diff,
                'address' => $contract->getRentAddress($contract->getProperty(), $contract->getUnit()),
        );
        return $this->sendBaseLetter($sTemplate, $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    public function sendListLateContracts($landlord, $tenants, $sTemplate = 'rjListLateContracts')
    {
        $vars = array(
            'nameLandlord' => $landlord->getFullName(),
            'tenants' => $tenants,
        );
        return $this->sendBaseLetter($sTemplate, $vars, $landlord->getEmail(), $landlord->getCulture());
    }

    public function sendRentReceipt(\CreditJeeves\DataBundle\Entity\Order $order, $sTemplate = 'rjOrderReceipt')
    {
        $tenant = $order->getUser();
        $history = $order->getHeartlands()->last();
        $fee = $order->getFee();
        $amount = $order->getSum();
        $total = $fee + $amount;
        $vars = array(
            'nameTenant' => $tenant->getFullName(),
            'datetime' => $order->getUpdatedAt()->format('m/d/Y H:i:s'),
            'transactionID' => $history ? $history->getTransactionId() : 'N/A',
            'amount' => $amount,
            'fee' => $fee,
            'total' => $total,
            'groupName' => $order->getGroupName(),
            'rentAmount' => $order->getRentAmount(),
            'otherAmount' => $order->getOtherAmount(),
        );
        return $this->sendBaseLetter($sTemplate, $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    public function sendRentError(\CreditJeeves\DataBundle\Entity\Order $order, $sTemplate = 'rjOrderError')
    {
        $tenant = $order->getContract()->getTenant();
        $fee = $order->getFee();
        $amount = $order->getSum();
        $total = $fee + $amount;
        $vars = array(
            'nameTenant' => $tenant->getFullName(),
            'datetime' => $order->getUpdatedAt()->format('m/d/Y H:i:s'),
            'amount' => $amount,
            'fee' => $fee,
            'total' => $total,
            'groupName' => $order->getGroupName(),
            'orderId' => $order->getId(),
            'error' => $order->getHeartlandErrorMessage(),
            'transactionId' => $order->getHeartlandTransactionId(),
            'rentAmount' => $order->getRentAmount(),
            'otherAmount' => $order->getOtherAmount(),
        );
        return $this->sendBaseLetter($sTemplate, $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    public function sendRjTenantInviteReminder(
        Tenant $tenant,
        Landlord $landlord,
        Contract $contract,
        $sTemplate = 'rjTenantInviteReminder'
    ) {
        $unit = $contract->getUnit();
        $vars = array(
            'fullNameLandlord'      => $landlord->getFullName(),
            'nameTenant'            => $tenant->getFirstName(),
            'address'               => $contract->getProperty()->getAddress(),
            'unitName'              => $unit ? $unit->getName() : '',
            'inviteCode'            => $tenant->getInviteCode(),
        );

        return $this->sendBaseLetter($sTemplate, $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    public function sendRjTenantInviteReminderPayment(
        $tenant,
        $landlord,
        $contract,
        $sTemplate = 'rjTenantInviteReminderPayment'
    ) {
        $unit = $contract->getUnit();
        $vars = array(
            'fullNameLandlord'      => $landlord->getFullName(),
            'nameTenant'            => $tenant->getFirstName(),
            'address'               => $contract->getProperty()->getAddress(),
            'unitName'              => $unit ? $unit->getName() : '',
        );
    }

    public function sendContractApprovedToTenant($contract, $sTemplate = 'rjContractApproved')
    {
        $tenant = $contract->getTenant();
        $vars = array(
            'nameTenant' => $tenant->getFullName(),
        );
        return $this->sendBaseLetter($sTemplate, $vars, $tenant->getEmail(), $tenant->getCulture());
    }


    public function sendRjContractRemovedFromDbByLandlord(
        $tenant,
        $landlord,
        $contract,
        $sTemplate = 'rjContractRemovedFromDbByLandlord'
    ) {
        $unit = $contract->getUnit();
        $vars = array(
            'fullNameLandlord'      => $landlord->getFullName(),
            'fullNameTenant'        => $tenant->getFullName(),
            'address'               => $contract->getProperty()->getAddress(),
            'unitName'              => $unit ? $unit->getName() : '',
        );

        return $this->sendBaseLetter($sTemplate, $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    public function sendRjContractRemovedFromDbByTenant(
        $tenant,
        $landlord,
        $contract,
        $sTemplate = 'rjContractRemovedFromDbByTenant'
    ) {
        $unit = $contract->getUnit();
        $vars = array(
            'fullNameLandlord'      => $landlord->getFullName(),
            'fullNameTenant'        => $tenant->getFullName(),
            'address'               => $contract->getProperty()->getAddress(),
            'unitName'              => $unit ? $unit->getName() : '',
        );

        return $this->sendBaseLetter($sTemplate, $vars, $landlord->getEmail(), $landlord->getCulture());
    }

    public function merchantNameSetuped($landlord, $group, $template = 'rjMerchantNameSetuped')
    {
        $vars = array(
            'fullNameLandlord'  => $landlord->getFullName(),
            'groupName'         => $group->getName(),
        );

        return $this->sendBaseLetter($template, $vars, $landlord->getEmail(), $landlord->getCulture());
    }

    public function endContractByLandlord($contract, $landlord, $tenant, $template = 'rjEndContract')
    {
        // Unit is a Doctrine Proxy, it always exists, but it throws an exception when we try to get unit's name
        try {
            $unitName = $contract->getUnit()->getName();
        } catch (Exception $e) {
            $unitName = '';
        }
        $vars = array(
            'tenantFullName'      => $tenant->getFullName(),
            'landlordFullName'    => $landlord->getFullName(),
            'uncollectedBalance' => $contract->getUncollectedBalance(),
            'address'             => $contract->getProperty()->getAddress(),
            'unitName'            => $unitName,
        );

        return $this->sendBaseLetter($template, $vars, $landlord->getEmail(), $landlord->getCulture());
    }

    public function sendOrderCancelToTenant(Order $order, $template = 'rjOrderCancel')
    {
        $tenant = $order->getContract()->getTenant();

        $vars = array(
            'tenantFullName' => $tenant->getFullName(),
            'orderStatus' => $order->getStatus(),
            'rentAmount' => $order->getSum(),
            'orderDate' => $order->getUpdatedAt()->format('m/d/Y H:i:s')
        );

        return $this->sendBaseLetter($template, $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    public function sendOrderCancelToLandlord(Order $order, $template = 'rjOrderCancelToLandlord')
    {
        $tenant = $order->getContract()->getTenant();
        $vars = array(
            'landlordFirstName' => '',
            'orderStatus' => $order->getStatus(),
            'rentAmount' => $order->getSum(),
            'orderDate' => $order->getUpdatedAt()->format('m/d/Y H:i:s'),
            'tenantName' => $tenant->getFullName(),
        );

        $group = $order->getContract()->getGroup();
        /** @var Landlord $landlord */
        foreach ($group->getGroupAgents() as $landlord) {
            $vars['landlordFirstName'] = $landlord->getFirstName();
            $this->sendBaseLetter($template, $vars, $landlord->getEmail(), $landlord->getCulture());
        }
    }

    public function sendPendingInfo(Order $order, $template = 'rjPendingOrder')
    {
        $tenant = $order->getContract()->getTenant();
        $history = $order->getHeartlands()->last();
        $amount = $order->getSum();
        $fee = $order->getFee();
        $total = $fee + $amount;
        $vars = array(
            'tenantName' => $tenant->getFullName(),
            'orderTime' => $order->getUpdatedAt()->format('m/d/Y H:i:s'),
            'transactionID' => $history ? $history->getTransactionId() : 'N/A',
            'amount' => $amount,
            'fee' => $fee,
            'total' => $total,
            'groupName' => $order->getGroupName(),
            'rentAmount' => $order->getRentAmount(),
            'otherAmount' => $order->getOtherAmount(),
        );
        return $this->sendBaseLetter($template, $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    public function sendContractAmountChanged(Contract $contract, Payment $payment)
    {
        $tenant = $contract->getTenant();
        $vars = array(
            'tenantName' => $tenant->getFullName(),
            'rentAmount' => $contract->getRent(),
            'paymentAmount' => $payment->getAmount(),
        );
        return $this->sendBaseLetter('rjContractAmountChanged', $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    /**
     * @param Landlord $landlord
     * @param array $groups
     * @param DateTime $date
     * @return bool
     */
    public function sendBatchDepositReportHolding(Landlord $landlord, $groups, DateTime $date)
    {
        $vars = [
            'landlordFirstName' => $landlord->getFirstName(),
            'date' => $date,
            'groups' => $groups,
        ];

        return $this->sendBaseLetter(
            'rjBatchDepositReportHolding',
            $vars,
            $landlord->getEmail(),
            $landlord->getCulture()
        );
    }

    /**
     * @param Landlord $landlord
     * @param Group $group
     * @param DateTime $date
     * @param array $batches
     * @return bool
     */
    public function sendBatchDepositReportLandlord(Landlord $landlord, Group $group, DateTime $date, $batches)
    {
        $vars = [
            'landlordFirstName' => $landlord->getFirstName(),
            'date' => $date,
            'groupName' => $group->getName(),
            'accountNumber' => $group->getDepositAccount()->getAccountNumber(),
            'batches' => $batches,
        ];

        return $this->sendBaseLetter(
            'rjBatchDepositReportLandlord',
            $vars,
            $landlord->getEmail(),
            $landlord->getCulture()
        );
    }
}
