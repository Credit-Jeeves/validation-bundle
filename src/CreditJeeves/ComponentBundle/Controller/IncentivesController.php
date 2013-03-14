<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class IncentivesController extends Controller
{
    public function indexAction()
    {
        $cjUser = $this->getUser();
        $Report = $cjUser->getReports()->last();
        $name   = '***';///$Report->getRawData();
        return $this->render('ComponentBundle:Incentives:index.html.twig', array('name' => $name));
    }
}
