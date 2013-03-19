<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\CoreBundle\Arf\ArfParser;


class AccountStatusController extends Controller
{
    public function indexAction()
    {
        $ArfReport      = $this->getUser()->getReportsPrequal()->last()->getArfReport();
        $sReportDate    = $this->getUser()->getReportsPrequal()->last()->getCreatedAt()->format('M j, Y');
        $TotalPastDue   = $ArfReport->getValue(
                        ArfParser::SEGMENT_PROFILE_SUMMARY,
                        ArfParser::REPORT_TOTAL_PAST_DUE
        );
        $DerogCounter   = $ArfReport->getValue(
                        ArfParser::SEGMENT_PROFILE_SUMMARY,
                        ArfParser::REPORT_DEROG_COUNTER
        );
        $ThirtyDaysLate = $ArfReport->getValue(
                        ArfParser::SEGMENT_PROFILE_SUMMARY,
                        ArfParser::REPORT_THIRTY_DATE_COUNTER
        );
        $SixtyDaysLate  = $ArfReport->getValue(
                        ArfParser::SEGMENT_PROFILE_SUMMARY,
                        ArfParser::REPORT_SIXTY_DATE_COUNTER
        );
        $NinetyDaysLate = $ArfReport->getValue(
                        ArfParser::SEGMENT_PROFILE_SUMMARY,
                        ArfParser::REPORT_NINETY_DATE_COUNTER
        );
        $aTradelines    = $ArfReport->createAccountStatus();
        $LateCounter    = $ArfReport->getLateCounter($aTradelines);
        $aClosed        = $ArfReport->getClosedTradelines();
        foreach ($aClosed as $nKey => $aValue) {
            if ($aValue['balance'] == 0) {
                unset($aClosed[$nKey]);
            }
        }
        return $this->render(
            'ComponentBundle:AccountStatus:index.html.twig',
            array(
                'sReportDate' => $sReportDate,
                'TotalPastDue' => $TotalPastDue,
                'LateCounter' => $LateCounter,
                'aTradelines' => $aTradelines,
                'aClosed' => $aClosed,
                )
            );
    }
}
