<?php

namespace RentJeeves\PublicBundle\Controller;

use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\PublicBundle\Form\LandlordAddressType;
use RentJeeves\DataBundle\Entity\Landlord;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\PublicBundle\Form\LandlordType;
use RentJeeves\DataBundle\Enum\ContractStatus;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LandlordController extends Controller
{
    /**
     * @Route("/landlord/register/", name="landlord_register")
     * @Template()
     *
     * @return array
     */
    public function registerAction()
    {
        $form = $this->createForm(
            new LandlordAddressType()
        );

        $request = $this->get('request');
        $propertyName = $request->get('searsh-field');

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $landlord = $form->getData()['landlord'];
                $address = $form->getData()['address'];
                $aForm = $request->request->get($form->getName());

                $password = $this->container->get('user.security.encoder.digest')
                        ->encodePassword($aForm['landlord']['password']['Password'], $landlord->getSalt());

                $landlord->setPassword($password);
                $address->setUser($landlord);
                $landlord->setCulture($this->container->parameters['kernel.default_locale']);

                $holding = new Holding();
                $holding->setName($landlord->getUsername());
                $landlord->setHolding($holding);
                $group = new Group();
                $group->setName($landlord->getUsername());
                $group->setHolding($holding);
                $holding->addGroup($group);
                $landlord->setAgentGroups($group);
                $em = $this->getDoctrine()->getManager();

                $property = $em->getRepository('RjDataBundle:Property')->find($aForm['property']);
                if ($property) {
                    $units = (isset($aForm['units']))? $aForm['units'] : array();
                    $property->addPropertyGroup($group);
                    $group->addGroupProperty($property);
                    if (!empty($units)) {
                        foreach ($units as $name) {
                            if (empty($name)) {
                                continue;
                            }
                            $unit = new Unit();
                            $unit->setProperty($property);
                            $unit->setHolding($holding);
                            $unit->setGroup($group);
                            $unit->setName($name);
                            $em->persist($unit);
                        }
                    }
                }

                $em->persist($address);
                $em->persist($holding);
                $em->persist($group);
                $em->persist($landlord);
                $em->flush();

                $this->get('project.mailer')->sendRjCheckEmail($landlord);
                return $this->redirect($this->generateUrl('user_new_send', array('userId' =>$landlord->getId())));
            }
        }

        return array(
            'form'          => $form->createView(),
            'propertyName'  => $propertyName,
        );
    }

    /**
     * @Route("/landlord/invite/{code}", name="landlord_invite")
     * @Template()
     *
     * @return array
     */
    public function landlordInviteAction($code)
    {
        $landlord = $this->getDoctrine()->getRepository('RjDataBundle:Landlord')->findOneBy(
            array(
                'invite_code' => $code
            )
        );

        if (empty($landlord)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $form = $this->createForm(
            new LandlordType(),
            $landlord
        );
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $landlord = $form->getData();
                $aForm = $request->request->get($form->getName());

                $password = $this->container->get('user.security.encoder.digest')
                        ->encodePassword($aForm['password']['Password'], $landlord->getSalt());

                $landlord->setPassword($password);
                $landlord->setCulture($this->container->parameters['kernel.default_locale']);
                $em = $this->getDoctrine()->getManager();
                $group = $landlord->getCurrentGroup();
                $contracts = $group->getContracts();
                if (!empty($contracts)) {
                    foreach ($contracts as $contract) {
                        $tenant = $contract->getTenant();
                        $this->get('project.mailer')->sendRjLandlordComeFromInvite(
                            $tenant,
                            $landlord,
                            $contract
                        );
                    }
                }

                $landlord->setInviteCode(null);
                $em->persist($landlord);
                $em->flush();
                return $this->login($landlord);
            }
        }

        return array(
            'code'      => $code,
            'form'      => $form->createView(),
        );
    }

    private function login($landlord)
    {
        $response = new RedirectResponse($this->generateUrl('landlord_tenants'));
        $this->container->get('fos_user.security.login_manager')->loginUser(
            $this->container->getParameter('fos_user.firewall_name'),
            $landlord,
            $response
        );

        $this->container->get('user.service.login_success_handler')
                ->onAuthenticationSuccess(
                    $this->container->get('request'),
                    $this->container->get('security.context')->getToken()
                );

        return $response;
    }
}
