<?php

namespace RentJeeves\ComponentBundle\Controller;

use RentJeeves\DataBundle\Entity\ContractRepository;
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
        $user = $this->getUser();
        $translator = $this->get('translator.default');
        $em = $this->getDoctrine()->getManager();

        $inviteCode = $user->getInviteCode();
        if (!empty($inviteCode)) {
            $alerts[] = $translator->trans('landlord.alert.verify_email');
        }

        if ($isSuperAdmin = $user->getIsSuperAdmin()) {
            $holding = $user->getHolding();
            $groups = $em->getRepository('DataBundle:Group')->getGroupsWithoutDepositAccount($holding);
            // alerts about merchant name
            foreach ($groups as $group) {
                $alerts[] = $translator->
                    trans(
                        'deposit.merchant.setup.admin',
                        array(
                            '%GROUP%' => $group->getName()
                        )
                    );
            }
            // alerts about pending contracts
            $groups = $em->getRepository('DataBundle:Group')->getGroupsWithPendingContracts($holding);
            foreach ($groups as $group) {
                $text = $translator->
                    transChoice(
                        'landlord.alert.pending-contract.admin',
                        $group['amount_pending'],
                        array(
                            '%count%' => $group['amount_pending'],
                            '%group%' => $group['group_name']
                        )
                    );
                $alerts[] = $text;
            }
        } else {
            $group = $this->get('core.session.landlord')->getGroup();
            $deposit = $group->getDepositAccount();
            $billing = $group->getActiveBillingAccount();

            if (empty($deposit) || $deposit->getStatus() == DepositAccountStatus::DA_INIT) {
                $alerts[] = $translator->trans('landlord.hps.contact_us_message');
            }
            if (!empty($deposit) && $deposit->getStatus() == DepositAccountStatus::HPS_SUCCESS) {
                $alerts[] = $translator->trans('landlord.hps.processing_message');
            }

            if (empty($billing)) {
                $alerts[] = $translator->trans(
                    'landlord.payment_account.set_up_message',
                    array('%payment_account_url%' => $this->generateUrl('settings_payment_accounts'))
                );
            }

            $pendingContractsCount = $em->getRepository('DataBundle:Group')->getCountPendingContracts($group);
            if ($pendingContractsCount > 0) {
                $text = $translator->transChoice(
                    'landlord.alert.pending-contracts.landlord',
                    $pendingContractsCount,
                    array('%COUNT%' => $pendingContractsCount)
                );

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
                break;
            }
        }
        if (!$hasPayment) {
            $alerts[] = $this->get('translator.default')->trans('alert.tenant.first_payment');
        }

        /** @var ContractRepository $contractRepo */
        $contractRepo = $this->getDoctrine()->getRepository('RjDataBundle:Contract');
        if (!$contractRepo->isTurnedOnBureauReporting($user)) {
            $alerts[] = $this->get('translator.default')->trans('alert.tenant.bureau_reporting');
        }

        return array(
            'alerts' => $alerts
        );
    }
}
