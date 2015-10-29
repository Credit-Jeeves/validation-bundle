<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\ArfBundle\Parser\ArfParser;
use CreditJeeves\DataBundle\Enum\HardInquiriesPeriod;

/**
 *
 * This is the Experian implementation of the ReportSummaryInterface.
 * Please see interface file for method documentation.
 *
 * The name "Prequal" is legacy and should probably be changed at some point.
 *
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\ReportPrequalRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ReportPrequal extends Report
{
    private $creditSummary = null;

    public function getBureauName()
    {
        return "Experian";
    }

    public function getUtilization()
    {
        $arfReport = $this->getArfReport();
        $revolvingDept = $arfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_TOTAL_REVOLVING
        );
        if ($revolvingDept == 0) {
            $availableDebt = 0;
        } else {
            $availableDebt = 100 - $arfReport->getValue(
                ArfParser::SEGMENT_PROFILE_SUMMARY,
                ArfParser::REPORT_TOTAL_REVOLVING_AVAILABLE_PERCENT
            );
        }

        return $availableDebt;
    }

    public function getBalanceRevolvingAccounts()
    {
        $arfReport = $this->getArfReport();
        $revolvingDept = $arfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_TOTAL_REVOLVING
        );

        return $revolvingDept ? $revolvingDept : 0;
    }

    public function getBalanceMortgageAccounts()
    {
        $arfReport = $this->getArfReport();
        $mortgageDebt = $arfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_REAL_ESTATE
        );

        return $mortgageDebt ? $mortgageDebt : 0;
    }

    public function getBalanceInstallmentAccounts()
    {
        $arfReport = $this->getArfReport();
        $installmentDebt = $arfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_INSTALLMENT
        );

        return $installmentDebt ? $installmentDebt : 0;
    }

    public function getTotalAccounts()
    {
        return $this->getCountApplicantTotalTradelines();
    }

    public function getTotalOpenAccounts()
    {
        return $this->getCountApplicantOpenedTradelines();
    }

    public function getTotalClosedAccounts()
    {
        return $this->getCountApplicantClosedTradelines();
    }

    public function getOldestTradelineInYears()
    {
        $oldest = 0;
        $tradelines = $this->getTradeLines();
        $currentDate = new \DateTime('now');
        if (!empty($tradelines)) {
            foreach ($tradelines as $tradeline) {
                $openedDate = \DateTime::createFromFormat('my', $tradeline['date_open']);
                if (empty($openedDate)) {
                    continue;
                }
                $interval = $openedDate->diff($currentDate);
                $months = $interval->format('%y') * 12 + $interval->format('%m');
                if ($months > $oldest) {
                    $oldest = $months;
                }
            }
        }

        return floor($oldest / 12);
    }

    public function getBalanceOpenCollectionAccounts()
    {
        return $this->getSummaryValue('total_past_due');
    }

    public function getTotalMonthlyPayments()
    {
        return $this->getSummaryValue('monthly_payment');
    }

    public function getTotalDerogatoryAccounts()
    {
        return $this->getSummaryValue('now_delinquentderog_counter');
    }

    public function getTotalOpenCollectionAccounts()
    {
        return $this->getCountTradelineCollections();
    }

    public function getTotalPublicRecords()
    {
        return $this->getSummaryValue('public_records_count');
    }

    public function getNumberOfInquiries()
    {
        return $this->getSummaryValue('total_inquiries_counter');
    }

    public function getInquiriesPeriod()
    {
        return HardInquiriesPeriod::SIX_MONTHS;
    }

    /**
     * @return array
     */
    protected function getSummary()
    {
        if (!$this->creditSummary) {
            $this->creditSummary = $this->getCreditSummary();
        }

        return $this->creditSummary;
    }

    protected function getSummaryValue($key)
    {
        if (isset($this->getSummary()[$key])) {
            return $this->getSummary()[$key];
        }

        return 0;
    }
}
