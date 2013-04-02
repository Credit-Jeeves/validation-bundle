<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class LearnMoreController extends Controller
{
    public function indexAction()
    {
        return $this->render('ComponentBundle:LearnMore:index.html.twig', array());
    }
}
