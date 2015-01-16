<?php

namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ReportTransunionSnapshot extends Report
{
    protected $snapshotData;

    protected function getSnapshotData($snapshotPiece)
    {
        if (!$this->snapshotData) {
            $this->snapshotData = simplexml_load_string($this->getRawData());
        }

        return (string)$this->snapshotData->$snapshotPiece;
    }

    /**
     * @return Float The balance of all revolving accounts. (i.e. 12345.67)
     */
    public function getBalanceRevolvingAccounts()
    {
        return $this->getSnapshotData('BalanceOpenRevolvingAccounts');
    }

    /**
     * @return Float The balance of all mortgage accounts. (i.e. 12345.67)
     */
    public function getBalanceMortgageAccounts()
    {
        return $this->getSnapshotData('BalanceOpenMortgageAccounts');
    }

    /**
     * @return Float The balance of all installment accounts. (i.e. 12345.67)
     */
    public function getBalanceInstallmentAccounts()
    {
        return $this->getSnapshotData('BalanceOpenInstallmentAccounts');
    }

    /**
     * @return Integer Total number of accounts/tradelines
     */
    public function getTotalAccounts()
    {
        return $this->getSnapshotData('TotalAccounts');
    }

    /**
     * @return Integer Total number of open accounts/tradelines
     */
    public function getTotalOpenAccounts()
    {
        return $this->getSnapshotData('OpenAccounts');
    }
    /**
     * @return Integer Total number of closed accounts/tradelines
     */
    public function getTotalClosedAccounts()
    {
        return $this->getSnapshotData('totalClosedAccounts');
    }

    /**
     * @return Integer Percentage of credit used
     */
    public function getUtilization()
    {
        return $this->getSnapshotData('Utilization');
    }

    /**
     * @return mixed
     */
    public function getNumberOfInquiries()
    {
        return $this->getSnapshotData('NumberOfInquiries');
    }

    /**
     * TODO: What should be here?
     *
     * @return mixed
     */
    public function getOldestTradelineInYears()
    {
        return $this->getSnapshotData('DateOfOldestTrade');
    }

    /**
     * @return mixed
     */
    public function getAgeOfCredit()
    {
        return $this->getSnapshotData('AgeOfCredit');
    }
}
