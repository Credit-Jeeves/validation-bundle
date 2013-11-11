<?php

namespace RentJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\DataBundle\Enum\ContractStatus;

class AlertController extends Controller
{
    /**
     * @Template
     *
     * @return array
     */
    public function indexAction()
    {
        $user = $this->getUser();
        $alerts = array();
        $group = $this->get("core.session.landlord")->getGroup();
        $contracts = $group->getContracts();
        $deposit = $group->getDepositAccount();
        $merchantName = $group->getMerchantName();
        $pending = 0;
        foreach ($contracts as $contract) {
            $status = $contract->getStatus();
            switch ($status) {
                case ContractStatus::PENDING:
                    $pending++;
                    break;
            }
        }
        if (empty($merchantName)) {
            $alerts[] = $this->get('translator.default')->trans('deposit.merchant.setup');
        }
        if ($pending > 0) {
            $text = $this->get('translator.default')->trans('landlord.alert.pending-one');
            if ($pending > 1) {
                $text = $this->get('translator.default')->trans(
                    'landlord.alert.pending-many',
                    array('%COUNT%' => $pending)
                );
            }
            $alerts[] = $text;
        }
        return array(
            'alerts' => $alerts
        );
    }

    /**
     * @Template("RjComponentBundle:Alert:index.html.twig")
     */
    public function tenantAction()
    {
        $alerts = array();
        $user = $this->getUser();
        $inviteCode = $user->getInviteCode();
        if (!empty($inviteCode)) {
            $alerts[] = $this->get('translator.default')->trans('alert.tenant.verify_email');
        }
        $hasPayment = false;
        $contracts = $user->getContracts();
        foreach ($contracts as $contract) {
            $payments = $contract->getPayments();
            if (count($payments) > 0) {
                $hasPayment = true;
            }
        }
        if (!$hasPayment) {
            $alerts[] = $this->get('translator.default')->trans('alert.tenant.first_payment');
        }
        return array(
            'alerts' => $alerts
        );
    }
}
