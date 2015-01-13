<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class TotalTradelinesController extends Controller
{
    public function indexAction(Report $Report)
    {
        $nOpened = $Report->getTotalOpenAccounts();
        $nClosed = $Report->getTotalClosedAccounts();
        $nTotal = $nOpened + $nClosed;

        return $this->render(
            'ComponentBundle:TotalTradelines:index.html.twig',
            array(
                'nTotal' => $nTotal,
                'nOpened' => $nOpened,
                'nClosed' => $nClosed,
                )
        );
    }
}
