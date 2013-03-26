<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class RightsController extends Controller
{
    /**
     * @Template()
     * @param \Report $Report
     */
    public function indexAction()
    {
        $Settings =  $this->getDoctrine()->getRepository('DataBundle:Settings')->find(1);
        $sRights = $Settings->getRights();
        return array(
                'sRights' => $sRights,
            );
    }
}
