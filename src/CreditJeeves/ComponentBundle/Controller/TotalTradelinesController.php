<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class TotalTradelinesController extends Controller
{
    public function indexAction(Report $Report)
    {
        $opened = $Report->getTotalOpenAccounts();
        $closed = $Report->getTotalClosedAccounts();
        $total = $opened + $closed;

        return $this->render(
            'ComponentBundle:TotalTradelines:index.html.twig',
            array(
                'total' => $total,
                'opened' => $opened,
                'closed' => $closed,
                )
        );
    }
}
