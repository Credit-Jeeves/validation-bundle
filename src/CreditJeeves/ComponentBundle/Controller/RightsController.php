<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class RightsController extends Controller
{
    /**
     * @Template()
     */
    public function indexAction()
    {
        $sRights = '';
        if ($Settings =  $this->getDoctrine()->getRepository('DataBundle:Settings')->find(1)) {
            $sRights = $Settings->getRights();
        }
        return array(
                'sRights' => $sRights,
            );
    }
}
