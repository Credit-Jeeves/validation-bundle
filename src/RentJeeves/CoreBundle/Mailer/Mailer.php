<?php
namespace RentJeeves\CoreBundle\Mailer;

use CreditJeeves\CoreBundle\Mailer\Mailer as BaseMailer;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Contract;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use JMS\DiExtraBundle\Annotation as DI;
use \Exception;
use \RuntimeException;

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

    public function sendRjPaymentDue($tenant, $holding, $contract, $sTemplate = 'rjPaymentDue')
    {
        $vars = array(
            'nameHolding' => $holding->getName(),
            'nameTenant' => $tenant->getFullName(),
            'address' => $contract->getRentAddress($contract->getProperty(), $contract->getUnit()),
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

    public function sendOrderReceipt(\CreditJeeves\DataBundle\Entity\Order $order, $sTemplate = 'rjOrderReceipt')
    {
        $tenant = $order->getTenant();
        $history = $order->getHeartlands()->last();
        $vars = array(
            'datetime' => $order->getUpdatedAt()->format('m/d/Y H:i:s'),
            'transactionID' => $history ? $history->getTransactionId() : 'N/A',
            'amount' => $order->getAmount(),
            'nameTenant' => $tenant->getFullName(),
        );
        return $this->sendBaseLetter($sTemplate, $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    public function sendOrderError(\CreditJeeves\DataBundle\Entity\Order $order, $sTemplate = 'rjOrderError')
    {
        $tenant = $order->getTenant();
        $vars = array(
        );
        return $this->sendBaseLetter($sTemplate, $vars, $tenant->getEmail(), $tenant->getCulture());
    }
}
