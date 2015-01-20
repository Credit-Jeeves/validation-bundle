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
        $tradelines = $Report->getTotalAccounts();
        $revolvingDept = $Report->getBalanceRevolvingAccounts();
        $mortgageDebt = $Report->getBalanceMortgageAccounts();
        $installmentDebt = $Report->getBalanceInstallmentAccounts();

        return $this->render(
            'ComponentBundle:LoanTypes:index.html.twig',
            array(
                'revolvingDept' => $revolvingDept,
                'mortgageDebt' => $mortgageDebt,
                'installmentDebt' => $installmentDebt,
                'tradelines' => $tradelines,
            )
        );
    }
}
