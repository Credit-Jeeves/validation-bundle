<?php

namespace RentJeeves\PublicBundle\Controller;

use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\PublicBundle\Form\InviteTenantType;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Alert;
use RentJeeves\PublicBundle\Form\TenantType;
use CreditJeeves\DataBundle\Enum\UserType;

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
     * @Route("/public_iframe", name="public_iframe")
     * @Template()
     *
     * @return array
     */
    public function publicIframeAction()
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
            $google = $this->container->get('google');
            $google->savePlace($Property);
            
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
                $password = $this->container->get('user.security.encoder.digest')
                        ->encodePassword($aForm['tenant']['password']['Password'], $tenant->getSalt());
                $tenant->setPassword($password);
                $invite->setTenant($tenant);
                $invite->setProperty($property);
                $tenant->setCulture($this->container->parameters['kernel.default_locale']);
                $em = $this->getDoctrine()->getManager();
                $em->persist($invite);
                $em->persist($tenant);
                $em->flush();

                $this->get('project.mailer')->sendRjCheckEmail($tenant);
                return $this->redirect($this->generateUrl('user_new_send', array('userId' =>$tenant->getId())));
            }
        }
        $view = $form->createView();

        return array(
            'address'   => $property->getFullAddress(),
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
        $request = $this->get('request');
        $em = $this->get('doctrine.orm.entity_manager');
        $google = $this->get('google');
        $propertyIdForm = (int)$request->request->get('propertyId');
        
        if ($propertyIdForm <= 0) {
            $Property = $em->getRepository('RjDataBundle:Property')
                ->findOneWithUnitAndAlphaNumericSort($propertyId);
        } else {
            $Property = $em->getRepository('RjDataBundle:Property')
                ->findOneWithUnitAndAlphaNumericSort($propertyIdForm);
        }

        if (!$Property) {
            return $this->redirect($this->generateUrl("iframe"));
        }

        $tenant = new Tenant();
        $form = $this->createForm(
            new TenantType(),
            $tenant
        );

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
                } elseif (!empty($unitNew) && $unitNew != 'none') {
                    $unitSearch = $unitNew;
                }

                $password = $this->container->get('user.security.encoder.digest')
                        ->encodePassword($aForm['password']['Password'], $tenant->getSalt());

                $tenant->setPassword($password);
                $tenant->setCulture($this->container->parameters['kernel.default_locale']);
                $em = $this->getDoctrine()->getManager();
                $em->persist($tenant);
                $em->flush();
                $Property->createContract($em, $tenant, $unitSearch);
                $this->get('project.mailer')->sendRjCheckEmail($tenant);
                return $this->redirect($this->generateUrl('user_new_send', array('userId' =>$tenant->getId())));
            }
        }

        $propertyList = $google->searchPropertyInRadius($Property);
        
        if (isset($propertyList[$Property->getId()])) {
            unset($propertyList[$Property->getId()]);
        }

        $propertyList = array_merge(array($Property), $propertyList);

        $countGroup = $em->getRepository('RjDataBundle:Property')->countGroup($Property->getId());

        if ($countGroup <= 0) {
            return $this->redirect($this->generateUrl("iframe_invite", array('propertyId'=>$propertyId)));
        }

        return array(
            'form'              => $form->createView(),
            'property'          => $Property,
            'propertyList'      => $propertyList,
            'countPropery'      => count($propertyList),
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
        $user = $this->getDoctrine()->getRepository('DataBundle:User')->findOneBy(array('invite_code' => $code));

        if (empty($user)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $user->setInviteCode(null);
        $user->setIsActive(true);
        $em->persist($user);
        if ($user->getType() == UserType::LANDLORD) {
            $em->flush();
            return array(
                'signinUrl' => $this->get('router')->generate('fos_user_security_login')
            );
        }
//         $alert = new Alert();
//         $alert->setMessage('rj.task.firstRent');
//         $alert->setUser($user);
//         $em->persist($alert);

        if ($user->getInvite()) {
            $invite = $user->getInvite();
            $this->get('invite.landord')->invite($invite, $user);
            $em->remove($invite);
        }

        $em->flush();

        return array(
            'signinUrl' => $this->get('router')->generate('fos_user_security_login')
        );
    }
}
