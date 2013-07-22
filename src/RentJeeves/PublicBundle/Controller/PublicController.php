<?php

namespace RentJeeves\PublicBundle\Controller;

use CreditJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\PublicBundle\Form\InviteTenantType;
use CreditJeeves\DataBundle\Entity\Tenant;
use RentJeeves\PublicBundle\Form\TenantType;

class PublicController extends Controller
{
    /**
     * @Route("/iframe", name="iframe")
     * @Template()
     *
     * @return array
     */
    public function iframeAction()
    {
        return array();
    }

    /**
     * @Route("/checkProperty/{propertyId}", name="iframe_search_check", options={"expose"=true})
     * @Template()
     *
     * @return array
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
     * @Route("/user/invite/{propertyId}", name="iframe_invite")
     * @Template()
     *
     * @return array
     */
    public function inviteAction($propertyId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $property = $em->getRepository('RjDataBundle:Property')->find($propertyId);
        
        if (!$property) {
            return $this->redirect($this->generateUrl("iframe"));
        }

        $form = $this->createForm(
            new InviteTenantType()
        );

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $tenant = $form->getData()['tenant'];
                $invite = $form->getData()['invite'];
                $aForm = $request->request->get($form->getName());
                $tenant->setPassword(md5($aForm['tenant']['password']['Password']));
                $invite->setTenant($tenant);
                $invite->setProperty($property);

                $em = $this->getDoctrine()->getManager();
                $em->persist($invite);
                $em->persist($tenant);
                $em->flush();

                $this->get('creditjeeves.mailer')->sendRjCheckEmail($tenant);
                return $this->redirect($this->generateUrl('user_new_send', array('tenantId' =>$tenant->getId())));
            }
        }
        $view = $form->createView();

        return array(
            'address'   => $property->getAddress(),
            'form'      => $form->createView(),
            'propertyId'=> $property->getId(),
        );
    }

    /**
     * @Route("/user/new/{propertyId}", name="iframe_new")
     * @Template()
     *
     * @return array
     */
    public function newAction($propertyId)
    {

        $em = $this->get('doctrine.orm.entity_manager');
        $Property = $em->getRepository('RjDataBundle:Property')->find($propertyId);
        
        if (!$Property) {
            return $this->redirect($this->generateUrl("iframe"));
        }
        // Here for development
        
        $countGroup = $em->getRepository('RjDataBundle:Property')->countGroup($Property->getId());

        if ($countGroup <= 0) {
            return $this->redirect($this->generateUrl("iframe_invite", array('propertyId'=>$propertyId)));
        }

        $tenant = new Tenant();
        $form = $this->createForm(
            new TenantType(),
            $tenant
        );

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $tenant = $form->getData();
                $aForm = $request->request->get($form->getName());
                $unitName = $request->request->get('unit'.$Property->getId());
                $unitNew = $request->request->get('unitNew'.$Property->getId());
                $unitSearch = null;
                if (!empty($unitName) && $unitName != 'new') {
                    $unitSearch = $unitName;
                } else if(!empty($unitNew)) {
                    $unitSearch = $unitNew;
                }
                $tenant->setPassword(md5($aForm['password']['Password']));
                $em = $this->getDoctrine()->getManager();
                $em->persist($tenant);
                $em->flush();
                $Property->createContract($em, $tenant, $unitSearch);
                $this->get('creditjeeves.mailer')->sendRjCheckEmail($tenant);
                return $this->redirect($this->generateUrl('user_new_send', array('tenantId' =>$tenant->getId())));
            }
        }

        return array(
            'form'      => $form->createView(),
            'property'  => $Property,
        );
    }

    /**
     * @Route("/user/check/{code}", name="tenant_new_check")
     * @Template()
     *
     * @return array
     */
    public function checkInviteAction($code)
    {
        $tenant = $this->getDoctrine()->getRepository('DataBundle:Tenant')->findOneBy(array('invite_code' => $code));

        if (empty($tenant)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $tenant->setInviteCode(null);
        $tenant->setIsActive(true);
        $em->flush();
        
        if ($tenant->getInvite()) {
            $this->get('creditjeeves.mailer')->sendRjLandLordInvite($tenant->getInvite());
        }

        return array(
            'signinUrl' => $this->get('router')->generate('fos_user_security_login')
        );
    }
}
