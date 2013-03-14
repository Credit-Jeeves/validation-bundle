<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class BuyReportController extends Controller
{
    public function indexAction()
    {
        $cjUser = $this->getUser();
        $name = __FILE__;
        return $this->render('ComponentBundle:BuyReport:index.html.twig', array('name' => $name));
    }
}
