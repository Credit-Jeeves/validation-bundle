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
        $group = $this->getUser()->getCurrentGroup();
        $contracts = $group->getContracts();
        $deposit = $group->getDepositAccount();
        $pending = 0;
        foreach ($contracts as $contract) {
            $status = $contract->getStatus();
            switch ($status) {
                case ContractStatus::PENDING:
                    $pending++;
                    break;
            }
        }
        if (empty($deposit)) {
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
}
