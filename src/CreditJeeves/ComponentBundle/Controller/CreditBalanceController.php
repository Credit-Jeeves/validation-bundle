<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;
use CreditJeeves\DataBundle\Entity\Score;

class CreditBalanceController extends Controller
{
    /**
     *
     * @var integer
     */
    const COMPONENT_WIDTH = 247;

    /**
     *
     * @var integer
     */
    const COMPONENT_POINTS = 500;

    public function indexAction(Report $Report, Score $Score)
    {
        $nTradelines = $Report->getTotalAccounts();
        $dateShortFormat = $this->container->getParameter('date_short');
        $sDate = $Report->getCreatedAt()->format($dateShortFormat);

        $nRevolvingDept = $Report->getBalanceRevolvingAccounts();
        $nMortgageDebt = $Report->getBalanceMortgageAccounts();
        $nInstallmentDebt = $Report->getBalanceInstallmentAccounts();

        $nTotal = $nRevolvingDept + $nInstallmentDebt + $nMortgageDebt;
        $nCurrentScore = $Score->getScore();
        $nCurrentScore = $nCurrentScore ? $nCurrentScore : 0;
        $nScale = self::COMPONENT_WIDTH / self::COMPONENT_POINTS;
        $nRight = intval((900 - $nCurrentScore) * $nScale - 37);
        if ($nRight > 300) {
            $nRight = 210;
        }
        $nPercent = $Score->getScorePercentage();
        return $this->render(
            'ComponentBundle:CreditBalance:index.html.twig',
            array(
                'sDate' => $sDate,
                'nRevolvingDept' => $nRevolvingDept,
                'nMortgageDebt' => $nMortgageDebt,
                'nInstallmentDebt' => $nInstallmentDebt,
                'nCurrentScore' => $nCurrentScore,
                'nRight' => $nRight,
                'nTotal' => $nTotal,
                'nPercent' => $nPercent,
                'nTradelines' => $nTradelines,
            )
        );
    }
}
