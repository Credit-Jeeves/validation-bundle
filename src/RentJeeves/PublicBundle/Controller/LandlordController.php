<?php

namespace RentJeeves\PublicBundle\Controller;

use CreditJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\PublicBundle\Form\LandlordAddressType;
use RentJeeves\DataBundle\Entity\Landlord;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\Unit;

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
                $landlord->setPassword(md5($aForm['landlord']['password']['Password']));
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

                $this->get('creditjeeves.mailer')->sendRjCheckEmail($landlord);
                return $this->redirect($this->generateUrl('user_new_send', array('userId' =>$landlord->getId())));
            }
        }

        return array(
            'form'          => $form->createView(),
            'propertyName'  => $propertyName,
        );
    }
}
