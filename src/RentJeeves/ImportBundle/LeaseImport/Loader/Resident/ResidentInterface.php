<?php

namespace RentJeeves\ImportBundle\LeaseImport\Loader\Resident;

use RentJeeves\DataBundle\Entity\Tenant;

interface ResidentInterface
{
    /**
     * @param string $externalResidentId
     */
    public function setExternalId($externalResidentId);

    /**
     * @return string|null
     */
    public function getExternalId();

    /**
     * @param int $accepted
     */
    public function setPaymentAccepted($accepted);

    /**
     * @return int
     */
    public function getPaymentAccepted();

    /**
     * @param bool $allowed
     */
    public function setPaymentAllowed($allowed);

    /**
     * @return bool
     */
    public function getPaymentAllowed();

    /**
     * @param string $status
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param Tenant $user
     */
    public function setUser(Tenant $user);

    /**
     * @return Tenant
     */
    public function getUser();

    /**
     * @param bool $isReporting
     */
    public function setReportToExperian($isReporting);

    /**
     * @return bool
     */
    public function getReportToExperian();

    /**
     * @param \DateTime $startAt
     */
    public function setExperianStartAt(\DateTime $startAt);

    /**
     * @return \DateTime
     */
    public function getExperianStartAt();

    /**
     * @param bool $isReporting
     */
    public function setReportToTransUnion($isReporting);

    /**
     * @return bool
     */
    public function getReportToTransUnion();

    /**
     * @param \DateTime $startAt
     */
    public function setTransUnionStartAt(\DateTime $startAt);

    /**
     * @return \DateTime
     */
    public function getTransUnionStartAt();

    /**
     * @param bool $isReporting
     */
    public function setReportToEquifax($isReporting);

    /**
     * @return bool
     */
    public function getReportToEquifax();

    /**
     * @param \DateTime $startAt
     */
    public function setEquifaxStartAt(\DateTime $startAt);

    /**
     * @return \DateTime
     */
    public function getEquifaxStartAt();
}
