<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class BuyReportController extends Controller
{
    public function indexAction()
    {
        $cjUser = $this->getUser();
        $Report = $cjUser->getReports()->last();
        $name   = $Report->getRawData();
        return $this->render('ComponentBundle:BuyReport:index.html.twig', array('name' => $name));
    }
}
