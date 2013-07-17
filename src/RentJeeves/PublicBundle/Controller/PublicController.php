<?php

namespace RentJeeves\PublicBundle\Controller;

use CreditJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\PublicBundle\Form\InviteTenantType;
use CreditJeeves\DataBundle\Entity\Tenant;

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
        $em = $this->get('doctrine.orm.entity_manager');
        $Property = $em->getRepository('RjDataBundle:Property')->find($propertyId);
        
        if (!$Property) {
            return $this->redirect($this->generateUrl("iframe"));
        }

        $tenant = new Tenant();
        $form = $this->createForm(
            new InviteTenantType(),
            $tenant
        );
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $tenant = $form->getData();
                $aForm = $request->request->get($form->getName());
                $tenant->setPassword(md5($aForm['password']['Password']));
                
                $em = $this->getDoctrine()->getManager();
                $em->persist($tenant);
                $em->flush();

                $this->get('creditjeeves.mailer')->sendRjCheckEmail($tenant);
                return $this->redirect($this->generateUrl('user_new_send', array('tenantId' =>$tenant->getId())));
            }
        }

        return array(
            'address'   => $Property->getAddress(),
            'form'      => $form->createView(),
            'propertyId'=> $Property->getId(),
        );
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
