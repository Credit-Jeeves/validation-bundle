<?php

namespace CreditJeeves\ApplicantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditJeeves\ApplicantBundle\Form\Type\LeadType;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\ApplicantBundle\Form\DataTransformer\CodeToGroupTransformer;

class ReturnedController extends Controller
{
    /**
     * @Route("/returned", name="applicant_returned")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $request = $this->get('request');
        $User = $this->get('core.session.applicant')->getUser();
        $Lead = new Lead();
        
        $Lead->setUser($User);
        
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
                $Lead = $form->getData();
                $Group = $Lead->getGroup();
                // @TODO would be fixed with right logic
                $Lead->setDealer($this->getLeadDealer($Lead));
                $Lead->setTargetScore($Group->getTargetScore());
                $Lead->setStatus($Lead::STATUS_NEW);
                $Lead->setSource('webpage');

                $em = $this->getDoctrine()->getManager();
                if ($this->validateLead($Lead)) {
                    $em->persist($Lead);
                }
                $User->setHasData(true);
                $em->persist($User);
                $em->flush();
            }
        }
        return array('form' => $form->createView());
    }

    private function validateLead($Lead)
    {
        return true;
    }

    private function getLeadDealer($Lead)
    {
        $Group = $Lead->getGroup();
        return $Group->getGroupDealers()->first();
    }
}
