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
                $invite = $tenant->getInvite();
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
     
        $countGroup = $em->getRepository('RjDataBundle:Property')->countGroup($Property->getId());

        if ($countGroup <= 0) {
            return $this->redirect($this->generateUrl("iframe_invite", array('propertyId'=>$propertyId)));
        }


        return array();
    }

    /**
     * @Route("/user/check/{code}", name="tenant_new_check")
     * @Template()
     *
     * @return array
     */
    public function checkInviteAction($code)
    {
        $user = $this->getDoctrine()->getRepository('DataBundle:User')->findOneBy(array('invite_code' => $code));

        if (empty($user)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $user->setInviteCode(null);
        $user->setIsActive(true);
        $em->flush();
        
        //@TODO: Write code for sending invite email to landlord

        return array(
            'signinUrl' => $this->get('router')->generate('fos_user_security_login')
        );
    }
}
