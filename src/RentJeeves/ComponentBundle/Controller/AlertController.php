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
        $contracts = $this->get('core.session.landlord')->getGroup()->getContracts();
        $invite = 0;
        $pending = 0;
        foreach ($contracts as $contract) {
            $status = $contract->getStatus();
            switch ($status) {
                case ContractStatus::INVITE:
                    $invite++;
                    break;
                case ContractStatus::PENDING:
                    $pending++;
                    break;
            }
        }
        if ($invite > 0) {
            $text = $this->get('translator.default')->trans('landlord.alert.invite-one');
            if ($invite > 1) {
                $text = $this->get('translator.default')->trans('landlord.alert.invite-many');
            }
            $alerts[] = $text;
        }
        if ($pending > 0) {
            $text = $this->get('translator.default')->trans('landlord.alert.pending-one');
            if ($pending > 1) {
                $text = $this->get('translator.default')->trans('landlord.alert.pending-many', array('%COUNT%' => $pending));
            }
            $alerts[] = $text;
        }
        return array(
            'alerts' => $alerts
        );
    }
}
