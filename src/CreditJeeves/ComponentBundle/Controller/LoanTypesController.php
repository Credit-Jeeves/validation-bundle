<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\CoreBundle\Arf\ArfParser;
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
        $nTradelines = $Report->getCountApplicantTotalTradelines();
        $ArfReport = $Report->getArfReport();
        $RevolvingDept = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_TOTAL_REVOLVING
        );
        $RevolvingDept = $RevolvingDept ? $RevolvingDept : 0;
        $MortgageDebt = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_REAL_ESTATE
        );
        $MortgageDebt = $MortgageDebt ? $MortgageDebt : 0;
        $InstallmentDebt = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_INSTALLMENT
        );
        $InstallmentDebt = $InstallmentDebt ? $InstallmentDebt : 0;
        return $this->render(
            'ComponentBundle:LoanTypes:index.html.twig',
            array(
                'RevolvingDept' => $RevolvingDept,
                'MortgageDebt' => $MortgageDebt,
                'InstallmentDebt' => $InstallmentDebt,
                'nTradelines' => $nTradelines,
            )
        );
    }
}
