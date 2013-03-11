<?php

namespace CreditJeeves\DealerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomepageController extends Controller
{
    public function indexAction()
    {
        return $this->render('DealerBundle:Homepage:index.html.twig', array());
    }
}
