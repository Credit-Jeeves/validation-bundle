<?php

namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class PublicController extends Controller
{
    /**
     * @Route("/public/search", name="tenant_search")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/public/check/{propertyId}", name="tenant_search_check", options={"expose"=true})
     * @Template()
     */
    public function checkSearchAction($propertyId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $Property = $em->getRepository('RjDataBundle:Property')->find($propertyId);
        
        if (!$Property) {
            return $this->redirect($this->generateUrl("tenant_search"));
        }

        $countGroup = $em->getRepository('RjDataBundle:Property')->countGroup($Property->getId());
        if ($countGroup > 0) {
            return $this->redirect($this->generateUrl("tenant_search_result", array('propertyId'=>$propertyId)));
        }

        return $this->redirect($this->generateUrl("tenant_search_empty", array('propertyId'=>$propertyId)));
    }

    /**
     * @Route("/public/emptySearch/{propertyId}", name="tenant_search_empty")
     * @Template()
     */
    public function emptySearchAction($propertyId)
    {
        return array();
    }

    /**
     * @Route("/public/searchResult/{propertyId}", name="tenant_search_result")
     * @Template()
     */
    public function searchResultAction($propertyId)
    {
        return array();
    }
}
