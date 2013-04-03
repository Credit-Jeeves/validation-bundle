<?php

namespace CreditJeeves\ApplicantBundle\Controller;

//use CreditJeeves\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\ApplicantBundle\Form\Type\NewType;
use CreditJeeves\UserBundle\Entity\NewEntity as User;

class NewController extends Controller
{
    public function indexAction()
    {
        $request = $this->get('request');
        $cjUser = new User();
        $form = $this->createForm(new NewType(), $cjUser);
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                
            }
        }
        return $this->render('ApplicantBundle:New:index.html.twig', array('form' => $form->createView()));
    }
}
