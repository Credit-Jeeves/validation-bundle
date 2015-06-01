<?php

namespace RentJeeves\CoreBundle\Report;

class RentalReportData
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $bureau;

    /**
     * @var \DateTime
     */
    protected $month;

    /**
     * @var \DateTime
     */
    protected $startDate;

    /**
     * @var \DateTime
     */
    protected $endDate;

    /**
     * @return string
     */
    public function getBureau()
    {
        return $this->bureau;
    }

    /**
     * @param string $bureau
     */
    public function setBureau($bureau)
    {
        $this->bureau = $bureau;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return \DateTime
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @param \DateTime $month
     */
    public function setMonth($month)
    {
        $this->month = $month;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
