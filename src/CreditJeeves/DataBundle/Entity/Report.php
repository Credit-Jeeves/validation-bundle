<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use CreditJeeves\DataBundle\Model\Report as BaseReport;
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
 * @ORM\DiscriminatorColumn(name="type", type="ReportType")
 * @ORM\DiscriminatorMap({"prequal" = "ReportPrequal", "d2c" = "ReportD2c"})
 * @ORM\Table(name="cj_applicant_report")
 * @ORM\HasLifecycleCallbacks()
 */
class Report extends BaseReport
{
    private $arfParser;

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
        return $this->getArfParser()->getArfArray();
    }

    /**
     * @return integer
     * @access public
     */
    public function getReportScore()
    {
        $oArfReport = $this->getArfReport();
        return intval($oArfReport->getValue(ArfParser::SEGMENT_RISK_MODEL, ArfParser::REPORT_SCORE));
    }

    /**
     * @return \CreditJeeves\CoreBundle\Arf\ArfParser
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
     */
    public function getTradelaineCollections($isSecurity = true)
    {
        $arfTradeLines = new ArfTradeLines($this->getArfArray());
        return $arfTradeLines->getCollections($isSecurity);
    }

    /**
     * @return array
     */
    public function getApplicantTradeLines($isSecurity = true)
    {
        $arfTradeLines = new ArfTradeLines($this->getArfArray());
        return $arfTradeLines->getAllTradelines($isSecurity);
    }

    /**
     * @return array
     */
    public function getApplicantNegativeTradeLines($isSecurity = true)
    {
        $arfTradeLines = new ArfTradeLines($this->getArfArray());
        return $arfTradeLines->getNegativeDetails($isSecurity);
    }

    /**
     * @return array
     */
    public function getApplicantSatisfactoryTradeLines($isSecurity = true)
    {
        $arfTradeLines = new ArfTradeLines($this->getArfArray());
        return $arfTradeLines->getSatisfactoryDetails($isSecurity);
    }

    /**
     * @return array
     */
    public function getApplicantIndefiniteTradeLines($isSecurity = true)
    {
        $arfTradeLines = new ArfTradeLines($this->getArfArray());
        return $arfTradeLines->getIndefiniteDetails($isSecurity);
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
     */
    public function getApplicantAddress()
    {
        $oArfReport = $this->getArfReport();
        $aAddresses = $oArfReport->getValue(ArfParser::SEGMENT_ADDRESS);
        return $aAddresses;
    }

    /**
     * @return array
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
     */
    public function getApplicantInquiries()
    {
        $arfInquiries = new ArfInquiries($this->getArfArray());
        return $arfInquiries->getInquiries();
    }

    /**
     * @return integer
     */
    public function getCountApplicantInquiries()
    {
        return count($this->getApplicantInquiries());
    }

    /**
     * @return array
     */
    public function getApplicantPublicRecords()
    {
        $arfPublicRecords = new ArfPublicRecords($this->getArfArray());
        return $arfPublicRecords->getPublicRecords();
    }

    /**
     * @return array
     */
    public function getApplicantAutomotiveDetails()
    {
        $arfAutomotiveDetails = new ArfAutomotiveDetails($this->getArfArray());
        return $arfAutomotiveDetails->getAutomotiveDetails();
    }

    /**
     * @return array
     */
    public function getApplicantMessages()
    {
        $arfMessages = new ArfMessages($this->getArfArray());
        return $arfMessages->getMessages();
    }

    /**
     * @return integer
     * @access public
     */
    public function getTotalMonthlyPayment()
    {
        $aSummary = $this->getApplicantSummaryInfo();
        return isset($aSummary['monthly_payment']) ? $aSummary['monthly_payment'] : 0;
    }

    
    /**
     * @return array
     * @access public
     */
    public function getApplicantSummaryInfo()
    {
        $arfSummaryInfo = new ArfSummary($this->getArfArray());
        return $arfSummaryInfo->getSummaryInfo();
    }
}
