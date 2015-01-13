<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class CreditCardUtilizationController extends Controller
{
    public function indexAction(Report $Report)
    {
        return $this->render(
            'ComponentBundle:CreditCardUtilization:index.html.twig',
            array(
                'nAvailableDebt' => $Report->getUtilization()
            )
        );
    }
}
