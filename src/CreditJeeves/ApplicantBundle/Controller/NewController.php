<?php

namespace CreditJeeves\ApplicantBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use CreditJeeves\ApplicantBundle\Form\Type\LeadType;
use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Entity\Group;

class NewController extends Controller
{
    /**
     * @Route("/new", name="applicant_new")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $request = $this->get('request');
        $query = $request->query;
        $Lead = new Lead();
        $User = new User();
        $Group = new Group();
        if ($request->getMethod() == 'GET') {
            // Group code
            if ($query->has('g')) {
                $Group->setCode($query->get('g'));
            }
            // User details
            $User = $this->bindUserDetails($User, $query);
        }
        $Lead->setUser($User);
        $Lead->setGroup($Group);
        $form = $this->createForm(
                new LeadType(),
                $Lead,
                array(
                        'em' => $this->getDoctrine()->getManager()
                )
        );
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
//                 echo '<pre>';
//                 print_r($request);
//                 echo '</pre>';
            }
        }
        return array(
            'form' => $form->createView()
            );
    }

    private function bindUserDetails($User, $query)
    {
        if ($query->has('fn')) {
            $User->setFirstName($query->get('fn'));
        }
        if ($query->has('mi')) {
            $User->setMiddleInitial($query->get('mi'));
        }
        if ($query->has('ln')) {
            $User->setLastName($query->get('ln'));
        }
        if ($query->has('ea')) {
            $User->setEmail($query->get('ea'));
        }
        if ($query->has('ea')) {
            $User->setEmail($query->get('ea'));
        }
        if ($query->has('s1')) {
            $User->setStreetAddress1($query->get('s1'));
        }
        if ($query->has('s2')) {
            $User->setUnitNo($query->get('s2'));
        }
        if ($query->has('ci')) {
            $User->setCity($query->get('ci'));
        }
        if ($query->has('st')) {
            $User->setState($query->get('st'));
        }
        if ($query->has('zp')) {
            $User->setZip($query->get('zp'));
        }
        if ($query->has('ph')) {
            $User->setPhone($query->get('ph'));
        }
        
        return $User;
    }
}
