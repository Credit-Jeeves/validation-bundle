<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Model\Report as BaseReport;
use \Exception;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="ReportType")
 * @ORM\DiscriminatorMap({
 *     "prequal" = "ReportPrequal",
 *     "d2c" = "ReportD2c",
 *     "tu_snapshot" = "ReportTransunionSnapshot"
 * })
 * @ORM\Table(name="cj_applicant_report")
 * @ORM\HasLifecycleCallbacks()
 */
class Report extends BaseReport implements ReportSummaryInterface
{
    /**
     * @throws Exception
     */
    public function getBalanceRevolvingAccounts()
    {
        $this->throwOverrideException();
    }

    /**
     * @throws Exception
     */
    public function getBalanceMortgageAccounts()
    {
        $this->throwOverrideException();
    }

    /**
     * @throws Exception
     */
    public function getBalanceInstallmentAccounts()
    {
        $this->throwOverrideException();
    }

    /**
     * @throws Exception
     */
    public function getBalanceOpenCollectionAccounts()
    {
        $this->throwOverrideException();
    }

    /**
     * @throws Exception
     */
    public function getTotalMonthlyPayments()
    {
        $this->throwOverrideException();
    }

    /**
     * @throws Exception
     */
    public function getTotalAccounts()
    {
        $this->throwOverrideException();
    }

    /**
     * @throws Exception
     */
    public function getTotalOpenAccounts()
    {
        $this->throwOverrideException();
    }

    /**
     * @throws Exception
     */
    public function getTotalClosedAccounts()
    {
        $this->throwOverrideException();
    }

    /**
     * @throws Exception
     */
    public function getTotalDerogatoryAccounts()
    {
        $this->throwOverrideException();
    }

    /**
     * @throws Exception
     */
    public function getTotalOpenCollectionAccounts()
    {
        $this->throwOverrideException();
    }

    /**
     * @throws Exception
     */
    public function getTotalPublicRecords()
    {
        $this->throwOverrideException();
    }

    /**
     * @throws Exception
     */
    public function getUtilization()
    {
        $this->throwOverrideException();
    }

    /**
     * @throws Exception
     */
    public function getNumberOfInquieres()
    {
        $this->throwOverrideException();
    }

    /**
     * @throws Exception
     */
    public function getOldestTradelineInYears()
    {
        $this->throwOverrideException();
    }

    /**
     * @throws Exception
     */
    public function getNumberOfInquiries()
    {
        $this->throwOverrideException();
    }

    /**
     * @throws Exception
     */
    public function getInquiriesPeriod()
    {
        $this->throwOverrideException();
    }

    /**
     * @throws Exception
     */
    public function getBureauName()
    {
        $this->throwOverrideException();
    }

    /**
     * @throws Exception
     */
    private function throwOverrideException()
    {
        throw new Exception("Your child Report class must implement this to support credit summary.");
    }
}
