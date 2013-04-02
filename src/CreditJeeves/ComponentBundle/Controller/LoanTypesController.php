<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\CoreBundle\Arf\ArfParser;

class LoanTypesController extends Controller
{
    /**
     *
     * @var integer
     */
    const MAX_DIAL = 12;
    
    public function indexAction()
    {
        $ArfReport = $this->get('core.session.applicant')->getUser()->getReportsPrequal()->last()->getArfReport();
        $RevolvingDept   = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_TOTAL_REVOLVING
        );
        $MortgageDebt    = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_REAL_ESTATE
        );
        $InstallmentDebt = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_INSTALLMENT
        );
        return $this->render(
            'ComponentBundle:LoanTypes:index.html.twig',
            array(
                'RevolvingDept' => $RevolvingDept,
                'MortgageDebt' => $MortgageDebt,
                'InstallmentDebt' => $InstallmentDebt,
                )
            );
    }
}
