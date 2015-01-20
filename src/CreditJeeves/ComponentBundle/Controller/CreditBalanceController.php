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
        $tradelines = $Report->getTotalAccounts();
        $dateShortFormat = $this->container->getParameter('date_short');
        $date = $Report->getCreatedAt()->format($dateShortFormat);

        $revolvingDept = $Report->getBalanceRevolvingAccounts();
        $mortgageDebt = $Report->getBalanceMortgageAccounts();
        $installmentDebt = $Report->getBalanceInstallmentAccounts();

        $total = $revolvingDept + $installmentDebt + $mortgageDebt;
        $currentScore = $Score->getScore();
        $currentScore = $currentScore ? $currentScore : 0;
        $scale = self::COMPONENT_WIDTH / self::COMPONENT_POINTS;
        $right = intval((900 - $currentScore) * $scale - 37);
        if ($right > 300) {
            $right = 210;
        }
        $percent = $Score->getScorePercentage();
        return $this->render(
            'ComponentBundle:CreditBalance:index.html.twig',
            array(
                'date' => $date,
                'revolvingDept' => $revolvingDept,
                'mortgageDebt' => $mortgageDebt,
                'installmentDebt' => $installmentDebt,
                'currentScore' => $currentScore,
                'right' => $right,
                'total' => $total,
                'percent' => $percent,
                'tradelines' => $tradelines,
            )
        );
    }
}
