<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ReportD2c extends Report
{
    /**
     * @ORM\OneToOne(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Operation",
     *     mappedBy="reportD2c",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $operation;

    /*
     *  This report does not currently implement the ReportSummaryInterface
     */

    /**
     * @throws \Exception
     */
    public function getBalanceRevolvingAccounts()
    {
        $this->notImplemented();
    }

    /**
     * @throws \Exception
     */
    public function getBalanceMortgageAccounts()
    {
        $this->notImplemented();
    }

    /**
     * @throws \Exception
     */
    public function getBalanceInstallmentAccounts()
    {
        $this->notImplemented();
    }

    /**
     * @throws \Exception
     */
    public function getBalanceOpenCollectionAccounts()
    {
        $this->notImplemented();
    }

    /**
     * @throws \Exception
     */
    public function getTotalMonthlyPayments()
    {
        $this->notImplemented();
    }

    /**
     * @throws \Exception
     */
    public function getTotalAccounts()
    {
        $this->notImplemented();
    }

    /**
     * @throws \Exception
     */
    public function getTotalOpenAccounts()
    {
        $this->notImplemented();
    }

    /**
     * @throws \Exception
     */
    public function getTotalClosedAccounts()
    {
        $this->notImplemented();
    }

    /**
     * @throws \Exception
     */
    public function getTotalDerogatoryAccounts()
    {
        $this->notImplemented();
    }

    /**
     * @throws \Exception
     */
    public function getTotalOpenCollectionAccounts()
    {
        $this->notImplemented();
    }

    /**
     * @throws \Exception
     */
    public function getTotalPublicRecords()
    {
        $this->notImplemented();
    }

    /**
     * @throws \Exception
     */
    public function getUtilization()
    {
        $this->notImplemented();
    }

    /**
     * @throws \Exception
     */
    public function getNumberOfInquieres()
    {
        $this->notImplemented();
    }

    /**
     * @throws \Exception
     */
    public function getOldestTradelineInYears()
    {
        $this->notImplemented();
    }

    /**
     * @throws \Exception
     */
    public function getNumberOfInquiries()
    {
        $this->notImplemented();
    }

    /**
     * @throws \Exception
     */
    public function getInquiriesPeriod()
    {
        $this->notImplemented();
    }

    /**
     * @throws \Exception
     */
    public function getBureauName()
    {
        $this->notImplemented();
    }

    /**
     * @throws \Exception
     */
    private function notImplemented()
    {
        throw new \Exception("Not implemented.");
    }
}
