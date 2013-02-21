<?php

namespace CreditJeeves\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomepageController extends Controller
{
    public function indexAction()
    {
        return $this->render('AdminBundle:Homepage:index.html.twig', array());
    }
}
