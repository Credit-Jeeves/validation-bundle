<?php

namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Enum\HardInquiriesPeriod;

/**
 *
 * This Report class implements ReportSummaryInterface.
 * Please see interface file for method documentation.
 *
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

    public function getBalanceRevolvingAccounts()
    {
        return $this->getSnapshotData('BalanceOpenRevolvingAccounts');
    }

    public function getBalanceMortgageAccounts()
    {
        return $this->getSnapshotData('BalanceOpenMortgageAccounts');
    }

    public function getBalanceInstallmentAccounts()
    {
        return $this->getSnapshotData('BalanceOpenInstallmentAccounts');
    }

    public function getTotalAccounts()
    {
        return $this->getSnapshotData('TotalAccounts');
    }

    public function getTotalOpenAccounts()
    {
        return $this->getSnapshotData('OpenAccounts');
    }

    public function getTotalClosedAccounts()
    {
        return $this->getSnapshotData('totalClosedAccounts');
    }

    public function getUtilization()
    {
        return $this->getSnapshotData('Utilization');
    }

    public function getBalanceOpenCollectionAccounts()
    {
        return $this->getSnapshotData('BalanceOpenCollectionAccounts');
    }

    public function getTotalMonthlyPayments()
    {
        return $this->getSnapshotData('TotalMonthlyPayments');
    }

    public function getTotalDerogatoryAccounts()
    {
        return $this->getSnapshotData('DerogatoryAccounts');
    }

    public function getTotalOpenCollectionAccounts()
    {
        return $this->getSnapshotData('TotalOpenCollectionAccounts');
    }

    public function getTotalPublicRecords()
    {
        return $this->getSnapshotData('totalPublicRecords');
    }

    public function getNumberOfInquiries()
    {
        return $this->getSnapshotData('NumberOfInquiries');
    }

    public function getOldestTradelineInYears()
    {
        $oldest = 0;
        $currentDate = new \DateTime('now');

        $openedDate = \DateTime::createFromFormat('Y-m-d', $this->getSnapshotData('DateOfOldestTrade'));
        if (!empty($openedDate)) {
            $interval = $openedDate->diff($currentDate);
            $months = $interval->format('%y') * 12 + $interval->format('%m');
            if ($months > $oldest) {
                $oldest = $months;
            }
        }

        return floor($oldest / 12);
    }

    public function getInquiriesPeriod()
    {
        return HardInquiriesPeriod::TWO_YEARS;
    }
}
