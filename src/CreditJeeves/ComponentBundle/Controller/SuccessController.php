<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SuccessController extends Controller
{
    /**
     * @Template()
     * @param \CreditJeeves\DataBundle\Entity\Lead $Lead
     * @return multitype:
     */
    public function indexAction(\CreditJeeves\DataBundle\Entity\Lead $Lead)
    {
        $sGroup = $Lead->getGroup()->getName();
        $sMessage = $Lead->getGroup()->getDescription();
        $sPhone = $Lead->getGroup()->getPhone();
        $nTarget = $Lead->getTargetScore();
        $cjUser = $this->get('security.context')->getToken()->getUser();
        $aAddress = $Lead->getGroup()->getAddressArray();
        return array(
            'nTarget' => $nTarget,
            'sGroup' => $sGroup,
            'sMessage' => $sMessage,
            'sPhone' => $sPhone,
            'aAddress' => $aAddress,
            );
    }
}
