<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class GoodsController extends Controller
{
    public function indexAction()
    {
        $cjUser = $this->getUser();
        //$Report = $cjUser->getReports()->last();
        $nTargetScore   = $cjUser->getLeads()->last()->getTargetScore();
//         switch ($sType) {
//             case ''
//         }
        return $this->render('ComponentBundle:Goods:index.html.twig', array('nTargetScore' => $nTargetScore));
    }
}
