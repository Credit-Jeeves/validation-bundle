<?php

namespace RentJeeves\ImportBundle\LeaseImport\Loader\Lease;

use RentJeeves\ImportBundle\LeaseImport\Loader\Resident\ResidentInterface;

interface LeaseInterface
{
    /**
     * @param ResidentInterface $resident
     */
    public function addResident(ResidentInterface $resident);

    /**
     * @param ResidentInterface $resident
     */
    public function removeResident(ResidentInterface $resident);

    /**
     * @param string $residentMapping
     */
    public function getResidentByResidentId($residentMapping);

    /**
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
