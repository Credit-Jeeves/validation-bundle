<?php
namespace RentJeeves\CoreBundle\Mailer;

use CreditJeeves\CoreBundle\Mailer\Mailer as BaseMailer;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Order;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Contract;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use JMS\DiExtraBundle\Annotation as DI;
use \Exception;
use \RuntimeException;
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


    public function sendRjTenantInvite($tenant, $landlord, $contract, $sTemplate = 'rjTenantInvite')
    {
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
        $vars = array(
            'nameTenant'            => $tenant->getFirstName(),
            'fullNameLandlord'      => $landlord->getFullName(),
            'address'               => $contract->getProperty()->getAddress(),
            'unitName'              => $contract->getUnit()->getName(),
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
        $tenant = $order->getContract()->getTenant();
        $history = $order->getHeartlands()->last();
        $type = $order->getType();
        $fee = 0;
        $amount = $order->getAmount();
        switch ($type) {
            case OrderType::HEARTLAND_CARD:
                $fee = round($amount * (float)$this->container->getParameter('payment_card_fee')) / 100;
                break;
            default:
                break;
        }
        $total = $fee + $amount;
        $vars = array(
            'nameTenant' => $tenant->getFullName(),
            'datetime' => $order->getUpdatedAt()->format('m/d/Y H:i:s'),
            'transactionID' => $history ? $history->getTransactionId() : 'N/A',
            'amount' => $order->getAmount(),
            'fee' => $fee,
            'total' => $total,
            'groupName' => $order->getGroupName(),
            'nameTenant' => $tenant->getFullName(),
        );
        return $this->sendBaseLetter($sTemplate, $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    public function sendRentError(\CreditJeeves\DataBundle\Entity\Order $order, $sTemplate = 'rjOrderError')
    {
        $tenant = $order->getContract()->getTenant();
        $type = $order->getType();
        $fee = 0;
        $amount = $order->getAmount();
        switch ($type) {
            case OrderType::HEARTLAND_CARD:
                $fee = round($amount * (float)$this->container->getParameter('payment_card_fee')) / 100;
                break;
            default:
                break;
        }
        $total = $fee + $amount;
        $vars = array(
            'nameTenant' => $tenant->getFullName(),
            'datetime' => $order->getUpdatedAt()->format('m/d/Y H:i:s'),
            'amount' => $order->getAmount(),
            'fee' => $fee,
            'total' => $total,
            'groupName' => $order->getGroupName(),
            'orderId' => $order->getId(),
            'error' => $order->getHeartlandErrorMessage(),
            'transactionId' => $order->getHeartlandTransactionId()
        );
        return $this->sendBaseLetter($sTemplate, $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    public function sendRjTenantInviteReminder($tenant, $landlord, $contract, $sTemplate = 'rjTenantInviteReminder')
    {
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
            'rentAmount' => $order->getAmount(),
            'orderDate' => $order->getUpdatedAt()->format('m/d/Y H:i:s')
        );

        return $this->sendBaseLetter($template, $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    public function sendOrderCancelToLandlord(Order $order, $template = 'rjOrderCancelToLandlord')
    {
        $vars = array(
            'landlordFirstName' => '',
            'orderStatus' => $order->getStatus(),
            'rentAmount' => $order->getAmount(),
            'orderDate' => $order->getUpdatedAt()->format('m/d/Y H:i:s')
        );

        $group = $order->getContract()->getGroup();
        foreach ($group->getGroupAgents() as $landlord) {
            $vars['landlordFirstName'] = $landlord->getFirstName();
            $this->sendBaseLetter($template, $vars, $landlord->getEmail(), $landlord->getCulture());
        }
    }

    public function sendPendingInfo(Order $order, $template = 'rjPendingOrder')
    {
        $tenant = $order->getContract()->getTenant();
        $history = $order->getHeartlands()->last();
        $amount = $order->getAmount();
        $fee = ($order->getType() == OrderType::HEARTLAND_CARD) ?
            round($amount * (float)$this->container->getParameter('payment_card_fee')) / 100 : 0;
        $total = $fee + $amount;
        $vars = array(
            'tenantName' => $tenant->getFullName(),
            'orderTime' => $order->getUpdatedAt()->format('m/d/Y H:i:s'),
            'transactionID' => $history ? $history->getTransactionId() : 'N/A',
            'amount' => $order->getAmount(),
            'fee' => $fee,
            'total' => $total,
            'groupName' => $order->getGroupName(),
        );
        return $this->sendBaseLetter($template, $vars, $tenant->getEmail(), $tenant->getCulture());
    }
}
