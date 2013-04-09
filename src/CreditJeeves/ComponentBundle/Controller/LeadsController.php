<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class LeadsController extends Controller
{
    /**
     * @Template
     *
     * @return array
     */
    public function indexAction()
    {
        $User = $this->get('core.session.applicant')->getUser();
        $Leads = $User->getUserLeads();
        $aLeads = array();
        foreach ($Leads as $Lead) {
            $aItem = array();
            $aItem['group'] = $Lead->getGroup()->getName();
            $aItem['id'] = $Lead->getId();
            $aItem['target'] = $Lead->getTargetScore();
            $aItem['type'] = $Lead->getGroup()->getType();
            $aItem['status'] = $Lead->getStatus();
            $aLeads[] = $aItem;
        }
        return array(
            'aLeads' => $aLeads,
            );
    }
}
