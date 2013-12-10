<?php

namespace RentJeeves\ComponentBundle\Controller;

use RentJeeves\DataBundle\Enum\DepositAccountStatus;
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
        $alerts = array();
        $user = $this->get('core.session.landlord')->getUser();
        if ($isSuperAdmin = $user->getIsSuperAdmin()) {
            $holding = $user->getHolding();
            $groups = $holding->getGroups();
            // alerts about merchant name
            foreach ($groups as $group) {
                $deposit = $group->getDepositAccount();
                if (empty($deposit)) {
                    $alerts[] = $this->get('translator.default')->
                        trans(
                            'deposit.merchant.setup.admin',
                            array(
                                '%GROUP%' => $group->getName()
                            )
                        );
                }
            }
            foreach ($groups as $group) {
                $pending = 0;
                $contracts = $group->getContracts();
                foreach ($contracts as $contract) {
                    $status = $contract->getStatus();
                    switch ($status) {
                        case ContractStatus::PENDING:
                            $pending++;
                            break;
                    }
                }
                if ($pending > 0) {
                    $text = $this->get('translator.default')->
                        trans(
                            'landlord.alert.pending-one.admin',
                            array(
                                '%GROUP%' => $group->getName()
                            )
                        );
                    if ($pending > 1) {
                        $text = $this->get('translator.default')->trans(
                            'landlord.alert.pending-many.admin',
                            array(
                                '%COUNT%' => $pending,
                                '%GROUP%' => $group->getName()
                            )
                        );
                    }
                    $alerts[] = $text;
                }
                
            }
        } else {
            $group = $this->get('core.session.landlord')->getGroup();
            $deposit = $group->getDepositAccount();
            $billing = $group->getBillingAccount();
            $contracts = $group->getContracts();
            $pending = 0;
            foreach ($contracts as $contract) {
                $status = $contract->getStatus();
                switch ($status) {
                    case ContractStatus::PENDING:
                        $pending++;
                        break;
                }
            }
            if (empty($deposit) || $deposit->getStatus() == DepositAccountStatus::DA_INIT) {
                $alerts[] = $this->get('translator.default')->trans(
                    'landlord.hps.complete_application',
                    array('%complete_url%' => $this->generateUrl('landlord_complete_account'))
                );
            }
            if (!empty($deposit) && $deposit->getStatus() == DepositAccountStatus::HPS_SUCCESS) {
                $alerts[] = $this->get('translator.default')->trans('landlord.hps.processing_message');
            }
            if (!empty($deposit) && $deposit->getStatus() == DepositAccountStatus::HPS_ERROR) {
                $alerts[] = $this->get('translator.default')->trans(
                    'landlord.hps.error_message',
                    array('%heartland_msg%' => $deposit->getMessage())
                );
            }
            if (empty($billing)) {
                $alerts[] = $this->get('translator.default')->trans('landlord.payment_account.set_up_message');
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
