<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\CoreBundle\Utility\Encryption;
use CreditJeeves\CoreBundle\Arf\ArfParser;
use CreditJeeves\CoreBundle\Arf\ArfReport;
use CreditJeeves\CoreBundle\Arf\ArfSummary;
use CreditJeeves\CoreBundle\Arf\ArfTradelines;


/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"prequal" = "ReportPrequal", "d2c" = "ReportD2c"})
 * @ORM\Table(name="cj_applicant_report")
 */
class Report
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
    protected $cj_applicant_id;

    /**
     * @ORM\Column(type="text")
     */
    protected $raw_data;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    private $arfParser;

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
     * @return Report
     */
    public function setCjApplicantId($cjApplicantId)
    {
        $this->cj_applicant_id = $cjApplicantId;
    
        return $this;
    }

    /**
     * Get cj_applicant_id
     *
     * @return integer 
     */
    public function getCjApplicantId()
    {
        return $this->cj_applicant_id;
    }

    /**
     * Set raw_data
     *
     * @param string $rawData
     * @return Report
     */
    public function setRawData($rawData)
    {
        $Utility = new Encryption();
        $this->raw_data = base64_encode(\cjEncryptionUtility::encrypt($rawData));

        return $this;
    }

    /**
     * Get raw_data
     *
     * @return string 
     */
    public function getRawData()
    {
        $Utility = new Encryption();
        $encValue = $this->raw_data;
        $value = \cjEncryptionUtility::decrypt(base64_decode($encValue));
        
        return $value === false ? $encValue : $value;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Report
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
     * @return array
     */
    public function getArfArray()
    {
        return $this->getArfParser()->getArfArray();
    }
    

    /**
     * @return CreditJeeves\CoreBundle\Arf\ArfPaser
     */
    public function getArfParser()
    {
        if ($this->arfParser == null) {
            $this->arfParser = new ArfParser($this->getRawData());
        }
        return $this->arfParser;
    }

    /**
     * @return CreditJeeves\CoreBundle\Arf\ArfReport
     */
    public function getArfReport()
    {
        return new ArfReport($this->getArfArray());
    }

    /**
     * @return array
     */
    public function getCreditSummary()
    {
        $arfSummaryInfo = new ArfSummary($this->getArfArray());
        return $arfSummaryInfo->getSummaryInfo();
    }

    /**
     * @return array
     * @access public
     */
    public function getTradelaineCollections($isSecurity = true)
    {
        $arfTradeLines = new ArfTradeLines($this->getArfArray());
        return $arfTradeLines->getCollections($isSecurity);
    }
    
    /**
     * @return integer
     * @access public
     */
    public function getCountTradelineCollections()
    {
        return count($this->getTradelaineCollections());
    }

    /**
     * @return array
     */
    public function getAutomotiveSummary()
    {
        $oArfReport = $this->getArfReport();
        return $oArfReport->getValue(ArfParser::SEGMENT_AUTOMOTIVE_PROFILE);
    }

    /**
     * @return array
     */
    public function getTradeLines()
    {
        $oArfReport = $this->getArfReport();
        $aTradeLines = $oArfReport->getValue(ArfParser::SEGMENT_TRADELINE);
        return $aTradeLines;
    }    
}
