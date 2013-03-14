<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class CreditBalanceController extends Controller
{
    public function indexAction()
    {
        $cjUser = $this->getUser();
        $Report = $cjUser->getReports()->last();
        $name = $cjUser->getEmail();
        return $this->render('ComponentBundle:CreditBalance:index.html.twig', array('name' => $name));
    }
}
