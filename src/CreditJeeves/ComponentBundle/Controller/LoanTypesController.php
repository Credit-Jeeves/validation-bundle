<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\ArfBundle\Parser\ArfParser;
use CreditJeeves\DataBundle\Entity\Report;

class LoanTypesController extends Controller
{
    /**
     *
     * @var integer
     */
    const MAX_DIAL = 12;

    public function indexAction(Report $Report)
    {
        $nTradelines = $Report->getTotalAccounts();
        $nRevolvingDept = $Report->getBalanceRevolvingAccounts();
        $nMortgageDebt = $Report->getBalanceMortgageAccounts();
        $nInstallmentDebt = $Report->getBalanceInstallmentAccounts();

        return $this->render(
            'ComponentBundle:LoanTypes:index.html.twig',
            array(
                'RevolvingDept' => $nRevolvingDept,
                'MortgageDebt' => $nMortgageDebt,
                'InstallmentDebt' => $nInstallmentDebt,
                'nTradelines' => $nTradelines,
            )
        );
    }
}
