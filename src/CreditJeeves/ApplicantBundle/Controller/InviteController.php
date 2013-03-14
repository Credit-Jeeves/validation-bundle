<?php

namespace CreditJeeves\ApplicantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class InviteController extends Controller
{
    public function indexAction($code)
    {
        return $this->render('ApplicantBundle:New:index.html.twig', array());
    }
}
