<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\CoreBundle\Arf\ArfParser;
use CreditJeeves\CoreBundle\Arf\ArfReport;

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

    public function indexAction()
    {
        $cjUser    = $this->get('security.context')->getToken()->getUser();
        $Report    = $cjUser->getReportsPrequal()->last();
        $sDate     = $Report->getCreatedAt()->format('M j, Y');
        $ArfReport = $Report->getArfReport();
        
        
        
        $nRevolvingDept   = intval(
                $ArfReport->getValue(
                        ArfParser::SEGMENT_PROFILE_SUMMARY,
                        ArfParser::REPORT_BALANCE_TOTAL_REVOLVING
                )
        );
        $nMortgageDebt    = intval(
                $ArfReport->getValue(
                        ArfParser::SEGMENT_PROFILE_SUMMARY,
                        ArfParser::REPORT_BALANCE_REAL_ESTATE
                )
        );
        $nInstallmentDebt = intval(
                $ArfReport->getValue(
                        ArfParser::SEGMENT_PROFILE_SUMMARY,
                        ArfParser::REPORT_BALANCE_INSTALLMENT
                )
        );
        $nTotal           = $nRevolvingDept + $nInstallmentDebt + $nMortgageDebt;
        $nCurrentScore    = $cjUser->getScores()->last()->getScore();
        $nScale           = self::COMPONENT_WIDTH / self::COMPONENT_POINTS;
        $nRight           = intval((900 - $nCurrentScore) * $nScale - 37);

        return $this->render(
            'ComponentBundle:CreditBalance:index.html.twig',
            array(
                'sDate' => $sDate,
                'nRevolvingDept' => $nRevolvingDept,
                'nMortgageDebt' => $nMortgageDebt,
                'nInstallmentDebt' => $nInstallmentDebt,
                'nCurrentScore' => $nCurrentScore,
                'nRight' => $nRight,
                'nTotal' => $nTotal
                )
            );
    }
}
