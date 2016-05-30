<?php

namespace RentJeeves\ImportBundle\Helper;

class LeaseEndDateCalculator
{
    const CURRENT = 'c';

    /**
     * @param \DateTime|null $leaseEnd
     * @param \DateTime|null $moveOut
     * @param string|null $tenantStatus
     * @param string|null $monthToMonth
     *
     * @return \DateTime|null
     */
    public static function calculateFinishAt(
        \DateTime $leaseEnd = null,
        \DateTime $moveOut = null,
        $tenantStatus = null,
        $monthToMonth = null
    ) {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        if ($moveOut !== null) {
            return $moveOut;
        }

        if (trim(strtolower($tenantStatus)) === self::CURRENT && ($leaseEnd !== null && $leaseEnd <= $today)) {
            // if tenant status is "C" and today is past lease-end,
            // then we should ignore month-to-month field and treat as month-to-month.
            return null;
        }

        if (trim(strtolower($monthToMonth)) === 'y') {
            return null;
        }

        if (trim(strtolower($monthToMonth === 'n')) && ($leaseEnd !== null && $leaseEnd <= $today)) {
            // Do not set the contract to finished for now. Promas customer data not perfect.
            // $this->setFinishedContract();
            return $leaseEnd;
        }

        return $leaseEnd;
    }
}

