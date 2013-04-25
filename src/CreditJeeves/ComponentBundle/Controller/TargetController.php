<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Utility\VehicleUtility as Vehicle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class TargetController extends Controller
{
    /**
     * @Template()
     * @param \CreditJeeves\DataBundle\Entity\Lead $Lead
     */
    public function indexAction(\CreditJeeves\DataBundle\Entity\Lead $Lead)
    {
        $User   = $this->get('core.session.applicant')->getUser();
        $nLeads      = $User->getUserLeads()->count();
        $Group       = $Lead->getGroup();
        $sType       = $Group->getType();
        $sLink       = $Group->getWebsiteUrl();
        $sTargetUrl  = $Lead->getTargetUrl();
        $sTargetName = $Lead->getTargetName();
        
        return array(
                        'sTargetUrl' => $sTargetUrl,
                        'sTargetName' => $sTargetName,
                        'sLink' => $sLink,
                        'nLeads' => $nLeads,
                    );
    }
}
