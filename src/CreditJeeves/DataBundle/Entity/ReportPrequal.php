<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Entity\Score;
use CreditJeeves\ArfBundle\Parser\ArfParser;
use CreditJeeves\DataBundle\Enum\HardInquiriesPeriod;

/**
 *
 * This is Experian implementation of the ReportSummaryInterface.
 * The name "Prequal" is legacy and should probably be changed at some point.
 *
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\ReportPrequalRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ReportPrequal extends Report
{
    private $oldest = null;

    private $nAge = null;

    private $creditSummary = null;

    public function getUtilization()
    {
        $ArfReport = $this->getArfReport();
        $nRevolvingDept = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_TOTAL_REVOLVING
        );
        if ($nRevolvingDept == 0) {
            $nAvailableDebt = 0;
        } else {
            $nAvailableDebt = 100 - $ArfReport->getValue(
                    ArfParser::SEGMENT_PROFILE_SUMMARY,
                    ArfParser::REPORT_TOTAL_REVOLVING_AVAILABLE_PERCENT
                );
        }

        return $nAvailableDebt;
    }

    public function getBalanceRevolvingAccounts()
    {
        $ArfReport = $this->getArfReport();
        $nRevolvingDept = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_TOTAL_REVOLVING
        );

        return $nRevolvingDept ? $nRevolvingDept : 0;
    }

    public function getBalanceMortgageAccounts()
    {
        $ArfReport = $this->getArfReport();
        $nMortgageDebt = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_REAL_ESTATE
        );

        return $nMortgageDebt ? $nMortgageDebt : 0;
    }

    public function getBalanceInstallmentAccounts()
    {
        $ArfReport = $this->getArfReport();
        $nInstallmentDebt = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_INSTALLMENT
        );

        return $nInstallmentDebt ? $nInstallmentDebt : 0;
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

    public function getNumberOfInquieres()
    {
        $ArfReport = $this->getArfReport();
        $nInquiries = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_INQUIRIES_DURING_LAST_6_MONTHS_COUNTER
        );

        return $nInquiries ? $nInquiries : 0;
    }

    public function getOldestTradelineInYears()
    {
        $this->oldest = 0;
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
                if ($months > $this->oldest) {
                    $this->oldest = $months;
                }
            }
        }

        return floor($this->oldest / 12);
    }

    /**
     * @return Float The balance of all accounts turned over to collections. (i.e. 12345.67)
     */
    public function getBalanceOpenCollectionAccounts()
    {
        return $this->getSummary()['total_past_due'];
    }

    /**
     * @return Float Monthly payment amounts due each month as reported by the creditor (i.e. 12345.67)
     */
    public function getTotalMonthlyPayments()
    {
        return $this->getSummary()['monthly_payment'];
    }

    /**
     * @return Integer Total number of derogatory accounts/tradelines
     */
    public function getTotalDerogatoryAccounts()
    {
        return $this->getSummary()['now_delinquentderog_counter'];
    }

    /**
     * @return Integer Total number of accounts/tradelines turned over to collections
     */
    public function getTotalOpenCollectionAccounts()
    {
        return $this->getCountTradelineCollections();
    }

    /**
     * @return Integer Total number of accounts/tradelines obtained from public records
     */
    public function getTotalPublicRecords()
    {
        return $this->getSummary()['public_records_count'];
    }

    /**
     * @return Integer Number of companies that viewed the consumer’s credit file over the last 6 months
     */
    public function getNumberOfInquiries()
    {
        return $this->getSummary()['total_inquiries_counter'];
    }

    /**
     * @return Array The window of time in which the value getNumberOfInquries was captured
     */
    public function getInquiriesPeriod()
    {
       return HardInquiriesPeriod::SIX_MONTHS;
    }

    /**
     * @return array
     */
    protected function getSummary()
    {
        if(!$this->creditSummary) {
            $this->creditSummary = $this->getCreditSummary();
        }

        return $this->creditSummary;
    }
}
