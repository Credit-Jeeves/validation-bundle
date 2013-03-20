<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GoodsController extends Controller
{
    public function indexAction(\CreditJeeves\DataBundle\Entity\Lead $Lead)
    {
        $cjUser = $this->getUser();
        //$Report = $cjUser->getReports()->last();
        $nTargetScore   = $Lead->getTargetScore();
//         switch ($sType) {
//             case ''
//         }
        return $this->render('ComponentBundle:Goods:index.html.twig', array('nTargetScore' => $nTargetScore));
    }
}
