<?php

namespace RentJeeves\PublicBundle\Controller;

use CreditJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class PublicController extends Controller
{
    /**
     * @Route("/iframe", name="iframe")
     * @Template()
     */
    public function iframeAction()
    {
        return array();
    }

    /**
     * @Route("/check/{propertyId}", name="iframe_search_check", options={"expose"=true})
     * @Template()
     */
    public function checkSearchAction($propertyId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $Property = $em->getRepository('RjDataBundle:Property')->find($propertyId);
        
        if (!$Property) {
            return $this->redirect($this->generateUrl("iframe"));
        }

        $countGroup = $em->getRepository('RjDataBundle:Property')->countGroup($Property->getId());
        if ($countGroup > 0) {
            return $this->redirect($this->generateUrl("iframe_new", array('propertyId'=>$propertyId)));
        }

        return $this->redirect($this->generateUrl("iframe_invite", array('propertyId'=>$propertyId)));
    }

    /**
     * @Route("/invite/{propertyId}", name="iframe_invite")
     * @Template()
     */
    public function inviteAction($propertyId)
    {
        return array();
    }

    /**
     * @Route("/new/{propertyId}", name="iframe_new")
     * @Template()
     */
    public function newAction($propertyId)
    {
        return array();
    }
}
