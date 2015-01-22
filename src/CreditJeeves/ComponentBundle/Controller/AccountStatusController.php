<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\ArfBundle\Parser\ArfParser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AccountStatusController extends Controller
{
    /**
     * This component currently only supports Experian prequal reports
     *
     * @Template()
     * @param \CreditJeeves\DataBundle\Entity\Report $Report
     * @return array
     */
    public function indexAction(\CreditJeeves\DataBundle\Entity\Report $Report)
    {
        $ArfReport = $Report->getArfReport();

        $dateShortFormat = $this->container->getParameter('date_short');
        $sReportDate = $Report->getCreatedAt()->format($dateShortFormat);

        $TotalPastDue = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_TOTAL_PAST_DUE
        );
        $DerogCounter = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_DEROG_COUNTER
        );
        $ThirtyDaysLate = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_THIRTY_DATE_COUNTER
        );
        $SixtyDaysLate = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_SIXTY_DATE_COUNTER
        );
        $NinetyDaysLate = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_NINETY_DATE_COUNTER
        );
        $aTradelines = $ArfReport->createAccountStatus();
        $LateCounter = $ArfReport->getLateCounter($aTradelines);
        $aClosed = $ArfReport->getClosedTradelines();
        foreach ($aClosed as $nKey => $aValue) {
            if ($aValue['balance'] == 0) {
                unset($aClosed[$nKey]);
            }
        }

        return array(
            'sReportDate' => $sReportDate,
            'TotalPastDue' => $TotalPastDue,
            'LateCounter' => $LateCounter,
            'aTradelines' => $aTradelines,
            'aClosed' => $aClosed,
        );
    }
}
