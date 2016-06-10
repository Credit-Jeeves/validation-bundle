<?php

namespace RentJeeves\ImportBundle\LeaseImport\Loader\Lease;

use RentJeeves\ImportBundle\LeaseImport\Loader\Resident\ResidentInterface;

interface LeaseInterface
{
    /**
     * @param ResidentInterface $resident
     *
     * NOTE: currently creates a new rj_contract record and associates tenant
     */
    public function addResident(ResidentInterface $resident);

    /**
     * @param ResidentInterface $resident
     *
     * NOTE: currently will FINISH the rj_contract record and delete the resident mapping
     * NOTE: if the last resident is removed, then this lease is effectively finished.
     */
    public function removeResident(ResidentInterface $resident);

    /**
     * Finds users that have already been imported from this accounting system
     *
     * @param string $residentMapping
     */
    public function getResidentByResidentId($residentMapping);

    /**
     * Finds users that were manually added/approved by PM
     *
     * @param string $email
     */
    public function getResidentByEmail($email);

    /**
     * @param int $day
     */
    public function setDueDate($day);

    /**
     * @return int
     */
    public function getDueDate();

    /**
     * @param float $amount
     */
    public function setRent($amount);

    /**
     * @return float
     */
    public function getRent();

    /**
     * @param float $balance
     */
    public function setBalance($balance);

    /**
     * @return float
     */
    public function getBalance();

    /**
     * @param \DateTime $date
     */
    public function setStartDate(\DateTime $date);

    /**
     * @return \DateTime
     */
    public function getStartDate();

    /**
     * @param \DateTime $date
     */
    public function setFinishDate(\DateTime $date);

    /**
     * @return \DateTime|null
     */
    public function getFinishDate();
}
