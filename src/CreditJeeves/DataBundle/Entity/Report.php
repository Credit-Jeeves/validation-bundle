<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\CoreBundle\Arf\ArfParser;
use CreditJeeves\CoreBundle\Arf\ArfReport;
use CreditJeeves\CoreBundle\Arf\ArfSummary;
use CreditJeeves\CoreBundle\Arf\ArfTradelines;
use CreditJeeves\CoreBundle\Arf\ArfDirectCheck;
use CreditJeeves\CoreBundle\Arf\ArfInquiries;
use CreditJeeves\CoreBundle\Arf\ArfPublicRecords;
use CreditJeeves\CoreBundle\Arf\ArfAutomotiveDetails;
use CreditJeeves\CoreBundle\Arf\ArfMessages;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="ReportTypeEnum")
 * @ORM\DiscriminatorMap({"prequal" = "ReportPrequal", "d2c" = "ReportD2c"})
 * @ORM\Table(name="cj_applicant_report")
 * @ORM\HasLifecycleCallbacks()
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
     * @ORM\Column(type="encrypt")
     */
    protected $raw_data;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    private $arfParser;

    /**
     * Cache
     *
     * @var array
     */
    private $arfArray;

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
        $this->raw_data = $rawData;
        return $this;
    }

    /**
     * Get raw_data
     *
     * @return string 
     */
    public function getRawData()
    {
        return $this->raw_data;
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
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->created_at = new \DateTime();
    }

    /**
     * @return array
     */
    public function getArfArray()
    {
        if (null == $this->arfArray) {
            $this->arfArray = $this->getArfParser()->getArfArray();
        }
        return $this->arfArray;
    }
    

    /**
     * @return \CreditJeeves\CoreBundle\Arf\ArfPaser
     */
    public function getArfParser()
    {
        if ($this->arfParser == null) {
            $this->arfParser = new ArfParser($this->getRawData());
        }
        return $this->arfParser;
    }

    /**
     * @return \CreditJeeves\CoreBundle\Arf\ArfReport
     */
    public function getArfReport()
    {
        return new ArfReport($this->getArfArray());
    }

    /**
     * @return array
     * @access public
     */
    public function getApplicantDirectCheck()
    {
        $arfDirectCheck = new ArfDirectCheck($this->getArfArray());
        return $arfDirectCheck->getDirectCheck();
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
     * @return array
     * @access public
     */
    public function getApplicantNegativeTradeLines($isSecurity = true)
    {
        $arfTradeLines = new ArfTradeLines($this->getArfArray());
        return $arfTradeLines->getNegativeDetails($isSecurity);
    }

    /**
     * @return array
     * @access public
     */
    public function getApplicantSatisfactoryTradeLines($isSecurity = true)
    {
        $arfTradeLines = new ArfTradeLines($this->getArfArray());
        return $arfTradeLines->getSatisfactoryDetails($isSecurity);
    }

    /**
     * @return integer
     */
    public function getCountApplicantTotalTradelines()
    {
        $arfTradeLines = new ArfTradeLines($this->getArfArray());
        return count($arfTradeLines->getTradeLines());
    }
    
    /**
     * @return integer
     */
    public function getCountApplicantOpenedTradelines()
    {
        $arfTradeLines = new ArfTradeLines($this->getArfArray());
        return count($arfTradeLines->getOpenedTradelines());
    }
    
    /**
     * @return integer
     */
    public function getCountApplicantClosedTradelines()
    {
        $arfTradeLines = new ArfTradeLines($this->getArfArray());
        return count($arfTradeLines->getClosedTradelines());
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

    /**
     * @return string
     * @access public
     */
    public function getApplicantName()
    {
        $oArfReport = $this->getArfReport();
        $aNames = $oArfReport->getValue(ArfParser::SEGMENT_NAME);
        return (isset($aNames['name_text'])) ? $aNames['name_text'] : $aNames[0]['name_text'];
    }

    /**
     *
     * @return array
     * @access public
     */
    public function getApplicantAddress()
    {
        $oArfReport = $this->getArfReport();
        $aAddresses = $oArfReport->getValue(ArfParser::SEGMENT_ADDRESS);
        return $aAddresses;
    }

    /**
     * @return array
     * @access public
     */
    public function getApplicantEmployments()
    {
        $oArfReport = $this->getArfReport();
        $aEmployments = array();
        $aResult = $oArfReport->getValue(ArfParser::SEGMENT_EMPLOYMENT);
        if (count($aResult) < 2) {
            $aEmployments[] = $aResult;
        } else {
            $aEmployments = $aResult;
        }
        return $aEmployments;
    }

    
    /**
     * @return array
     * @access public
     */
    public function getApplicantInquiries()
    {
        $arfInquiries = new ArfInquiries($this->getArfArray());
        return $arfInquiries->getInquiries();
    }

    /**
     * @return integer
     * @access public
     */
    public function getCountApplicantInquiries()
    {
        return count($this->getApplicantInquiries());
    } 

    /**
     * @return array
     * @access public
     */
    public function getApplicantPublicRecords()
    {
        $arfPublicRecords = new ArfPublicRecords($this->getArfArray());
        return $arfPublicRecords->getPublicRecords();
    }

    /**
     * @return array
     * @access public
     */
    public function getApplicantAutomotiveDetails()
    {
        $arfAutomotiveDetails = new ArfAutomotiveDetails($this->getArfArray());
        return $arfAutomotiveDetails->getAutomotiveDetails();
    }

    /**
     * @return array
     * @access public
     */
    public function getApplicantMessages()
    {
      $arfMessages = new ArfMessages($this->getArfArray());
    
      return $arfMessages->getMessages();
    }    
}
