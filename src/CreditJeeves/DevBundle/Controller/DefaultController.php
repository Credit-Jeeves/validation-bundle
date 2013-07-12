<?php

namespace CreditJeeves\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CreditJeevesDevBundle:Default:index.html.twig', array('name' => $name));
    }
}
