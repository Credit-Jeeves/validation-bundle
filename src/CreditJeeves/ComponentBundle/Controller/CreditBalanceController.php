<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\ArfBundle\Parser\ArfParser;
use CreditJeeves\ArfBundle\Map\ArfReport;
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
        $nTradelines = $Report->getCountApplicantTotalTradelines();
        $dateShortFormat = $this->container->getParameter('date_short');
        $sDate = $Report->getCreatedAt()->format($dateShortFormat);
        
        $ArfReport = $Report->getArfReport();


        $nRevolvingDept = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_TOTAL_REVOLVING
        );
        $nRevolvingDept = $nRevolvingDept ? $nRevolvingDept : 0;
        $nMortgageDebt = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_REAL_ESTATE
        );
        $nMortgageDebt = $nMortgageDebt ? $nMortgageDebt : 0;
        $nInstallmentDebt = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_INSTALLMENT
        );
        $nInstallmentDebt = $nInstallmentDebt ? $nInstallmentDebt : 0;
        $nTotal = $nRevolvingDept + $nInstallmentDebt + $nMortgageDebt;
        $nCurrentScore = $Score->getScore();
        $nCurrentScore = $nCurrentScore ? $nCurrentScore : 0;
        $nScale = self::COMPONENT_WIDTH / self::COMPONENT_POINTS;
        $nRight = intval((900 - $nCurrentScore) * $nScale - 37);
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
