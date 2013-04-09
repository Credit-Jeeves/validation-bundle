<?php

namespace CreditJeeves\ApplicantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditJeeves\ApplicantBundle\Form\Type\ReturnedType;
use CreditJeeves\ApplicantBundle\Form\Entity\User;

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
        $form = $this->createForm(new ReturnedType(), $User);
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
        
            }
        }
        return array('form' => $form->createView());
    }
}
