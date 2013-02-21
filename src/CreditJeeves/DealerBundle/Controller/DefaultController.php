<?php

namespace CreditJeeves\DealerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('DealerBundle:Default:index.html.twig', array('name' => $name));
    }
}
