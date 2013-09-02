<?php

namespace RentJeeves\TenantBundle\Controller;

use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AlertController extends Controller
{
    /**
     * @Template()
     */
    public function indexAction()
    {
        $data = array();
        if (!$this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return array('alertMessages' => $data);
        }

        $user = $this->getUser();

        $alerts = $user->getAlert();
        if (empty($alerts)) {
            return array('alertMessages' => $data);
        }

        for ($i=0; $i <= count($alerts)-1; $i++) {
            $data[$i+1] = $this->get('translator')->trans($alerts[$i]->getMessage());
        }

        return array('alertMessages' => $data);
    }
}
