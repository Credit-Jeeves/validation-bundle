<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class SimulationController extends Controller
{
    public function indexAction()
    {
        $cjUser = $this->get('security.context')->getToken()->getUser();
        $Report = $cjUser->getReportsPrequal()->last();
        $name = $cjUser->getEmail();
        return $this->render('ComponentBundle:Simulation:index.html.twig', array('name' => $name));
    }
}
