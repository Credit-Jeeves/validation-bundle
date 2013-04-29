<?php
namespace CreditJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Enum\AtbType;

/**
 * @ORM\MappedSuperclass
 */
abstract class Atb
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $cj_applicant_report_id;

    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\ReportPrequal")
     * @ORM\JoinColumn(name="cj_applicant_report_id", referencedColumnName="id")
     */
    protected $report;

    /**
     * @ORM\Column(type="AtbType")
     */
    protected $type;

    /**
     *
     * @ORM\Column(type="integer")
     */
    protected $input;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sim_type;

    /**
     * @ORM\Column(type="string")
     */
    protected $transaction_signature;

    /**
     * @ORM\Column(type="encrypt")
     */
    protected $result;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set cj_applicant_id
     *
     * @param integer $cjApplicantId
     * @return Atb
     */
    public function setReportId($reportId)
    {
        $this->cj_applicant_id = $reportId;

        return $this;
    }

    /**
     * Get cj_applicant_id
     *
     * @return integer
     */
    public function getReportId()
    {
        return $this->cj_applicant_id;
    }

    /**
     * Set type
     *
     * @param AtbType $type
     * @return Atb
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return AtbType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set input
     *
     * @param integer $input
     * @return Atb
     */
    public function setInput($input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Get input
     *
     * @return integer
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Set sim_type
     *
     * @param integer $simType
     * @return Atb
     */
    public function setSimType($simType)
    {
        $this->sim_type = $simType;

        return $this;
    }

    /**
     * Get sim_type
     *
     * @return integer
     */
    public function getSimType()
    {
        return $this->sim_type;
    }

    /**
     * Set transaction_signature
     *
     * @param string $transactionSignature
     * @return Atb
     */
    public function setTransactionSignature($transactionSignature)
    {
        $this->transaction_signature = $transactionSignature;

        return $this;
    }

    /**
     * Get transaction_signature
     *
     * @return string
     */
    public function getTransactionSignature()
    {
        return $this->transaction_signature;
    }

    /**
     * Set result
     *
     * @param string $result
     * @return Atb
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Get result
     *
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Atb
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return Atb
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set report
     *
     * @param \CreditJeeves\DataBundle\Entity\ReportPrequal $report
     * @return Atb
     */
    public function setReport(\CreditJeeves\DataBundle\Entity\ReportPrequal $report = null)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report
     *
     * @return \CreditJeeves\DataBundle\Entity\ReportPrequal
     */
    public function getReport()
    {
        return $this->report;
    }
}
