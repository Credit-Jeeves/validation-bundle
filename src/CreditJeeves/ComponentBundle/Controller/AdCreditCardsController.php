<?php
namespace CreditJeeves\ComponentBundle\Controller;

use CreditJeeves\DataBundle\Entity\AtbRepository;
use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\SimulationBundle\Enum\AtbType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditJeeves\DataBundle\Entity\Report;
use CreditJeeves\DataBundle\Entity\Score;
use CreditJeeves\ArfBundle\Parser\ArfParser;
use CreditJeeves\SimulationBundle\AtbSimulation;

class AdCreditCardsController extends Controller
{
    /**
     *
     * This component currently only supports Experian prequal reports
     *
     * @Template
     *
     * @return array
     */
    public function indexAction(Report $Report, Lead $Lead = null)
    {
        $cjArfReport = $Report->getArfReport();
        $nInquiries = $cjArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_INQUIRIES_DURING_LAST_6_MONTHS_COUNTER
        );
        $sInquiries = $this->formatInquiries($nInquiries);
        $nRevolvingDept   = $cjArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_TOTAL_REVOLVING
        );
        $isRevolving = empty($nRevolvingDept) ? "false" : "true";
        $nMortgageDebt    = $cjArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_REAL_ESTATE
        );
        $isMortgage = empty($nMortgageDebt) ? "false" : "true";
        $nTotal = $Report->getTotalAccounts();
        $sTotal = $this->formatTradelinesCount($nTotal);
         $nTier = array('value' => 9, 'interval' => '< 300');
        $nScore = $this->get('core.session.applicant')->getUser()->getLastScore();
        if ($nScore) {
            $nTier = Score::getTier($nScore);
        }
        $nAvailableDebt = 100 - $cjArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_TOTAL_REVOLVING_AVAILABLE_PERCENT
        );
        $sUtilization = $this->formatUtilization($nAvailableDebt);
        $aRecords = $Report->getApplicantPublicRecords();
        $sBankruptcy = $this->formatBankruptcy($aRecords);

        $simTypeGroup = 'NA';
        if (!empty($Lead)) {
            /** @var AtbRepository $repo */
            $repo = $this->get('doctrine.orm.default_entity_manager')->getRepository('DataBundle:Atb');
            $atb = $repo->findLatsSimulationEntity(
                $Report->getId(),
                $Lead->getTargetScore(),
                array(AtbType::SCORE, AtbType::SEARCH)
            );

            if (!empty($atb)) {
                $simTypeGroup = $atb->getSimType() ? substr((string)$atb->getSimType(), 0, 2) : null;
            }
        }
        return array(
            'isMortgage' => $isMortgage,
            'isRevolving' => $isRevolving,
            'sUtilization' => $sUtilization,
            'nTier' => $nTier,
            'sTotal' => $sTotal,
            'sInquiries' => $sInquiries,
            'sBankruptcy' => $sBankruptcy,
            'simTypeGroup' => $simTypeGroup,
        );
    }

    /**
     *
     * @param integer $nInquiries
     * @return string
     */
    private function formatInquiries($nInquiries)
    {
        $sInquiries = "1-5";
        if ($nInquiries < 1) {
            $sInquiries = "0";
        }
        if ($nInquiries > 5) {
            $sInquiries = "5plus";
        }
        return $sInquiries;
    }

    /**
     *
     * @param integer $nTotal
     * @return string
     */
    private function formatTradelinesCount($nTotal)
    {
        $sTotal = "0";
        if ($nTotal > 30) {
            return "30plus";
        }
        if ($nTotal > 20) {
            return "21-30";
        }
        if ($nTotal > 15) {
            return "16-20";
        }
        if ($nTotal > 10) {
            return "11-15";
        }
        if ($nTotal > 6) {
            return "7-10";
        }
        if ($nTotal > 3) {
            return "4-6";
        }
        if ($nTotal > 0) {
            return "1-3";
        }
        return $nTotal;
    }

    /**
     *
     * @param integer $nAvailableDebt
     * @return string
     */
    private function formatUtilization($nAvailableDebt)
    {
        $sUtilization = "0-30";
        if ($nAvailableDebt > 90) {
            return "90-100";
        }
        if ($nAvailableDebt > 70) {
            return "70-90";
        }
        if ($nAvailableDebt > 50) {
            return "50-70";
        }
        if ($nAvailableDebt > 30) {
            return "30-50";
        }
        return $sUtilization;
    }

    /**
     *
     * @param array $aPublicRecords
     * @return string
     */
    private function formatBankruptcy($aPublicRecords)
    {
        $sBankruptcy = "none";
        $Now = new \DateTime();
        $nMonths = 0;
        foreach ($aPublicRecords as $Record) {
            if (in_array($Record['code'], array(13, 23, 25, 27))) {
                $Date = \DateTime::createFromFormat('m/d/y', $Record['status_date']);
                $interval = $Now->diff($Date);
                $nDiff = $interval->format('%m');
                if ($nDiff > $nMonths) {
                    $nMonths = $nDiff;
                }
            }
        }
        if ($nMonths > 12) {
            return "bey12";
        }
        if ($nMonths > 0) {
            return "win12";
        }
        return $sBankruptcy;
    }
}
