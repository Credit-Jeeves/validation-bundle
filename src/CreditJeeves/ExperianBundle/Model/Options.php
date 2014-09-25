<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Options")
 */
class Options
{
    /**
     * @Serializer\SerializedName("BrokerNumber")
     * @Serializer\Groups({"PreciseID"})
     * @var
     */
    protected $brokerNumber = null;

    /**
     * @Serializer\SerializedName("EndUser")
     * @Serializer\Groups({"PreciseID"})
     * @var
     */
    protected $endUser = null;

    /**
     * @Serializer\SerializedName("FreezeKeyPIN")
     * @Serializer\Groups({"PreciseID"})
     * @var
     */
    protected $freezeKeyPIN = null;

    /**
     * @Serializer\SerializedName("ReferenceNumber")
     * @Serializer\Groups({"PreciseID"})
     * @var
     */
    protected $referenceNumber = null;

    /**
     * @Serializer\SerializedName("OFAC")
     * @Serializer\Groups({"PreciseID"})
     * @var string
     */
    protected $ofac = 'Y';

    /**
     * @Serializer\SerializedName("OFACMSG")
     * @Serializer\Groups({"PreciseID"})
     * @var string
     */
    protected $ofacmsg = 'Y';

    /**
     * @Serializer\SerializedName("PreciseIDType")
     * @Serializer\Groups({"PreciseID"})
     * @var int
     */
    protected $preciseIDType = 11;

    /**
     * @Serializer\SerializedName("DetailRequest")
     * @Serializer\Groups({"PreciseID"})
     * @var string
     */
    protected $detailRequest = 'D';

    /**
     * @Serializer\SerializedName("InquiryChannel")
     * @Serializer\Groups({"PreciseID"})
     * @var string
     */
    protected $inquiryChannel = 'INTE';

    /**
     * @Serializer\SerializedName("AccessChannel")
     * @Serializer\Groups({"PreciseID"})
     * @var string
     */
    protected $accessChannel = 'PPQ';

    /**
     * @return string
     */
    public function getAccessChannel()
    {
        return $this->accessChannel;
    }

    /**
     * @param string $accessChannel
     *
     * @return $this
     */
    public function setAccessChannel($accessChannel)
    {
        $this->accessChannel = $accessChannel;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBrokerNumber()
    {
        return $this->brokerNumber;
    }

    /**
     * @param mixed $brokerNumber
     *
     * @return $this
     */
    public function setBrokerNumber($brokerNumber)
    {
        $this->brokerNumber = $brokerNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getDetailRequest()
    {
        return $this->detailRequest;
    }

    /**
     * @param string $detailRequest
     *
     * @return $this
     */
    public function setDetailRequest($detailRequest)
    {
        $this->detailRequest = $detailRequest;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEndUser()
    {
        return $this->endUser;
    }

    /**
     * @param mixed $endUser
     *
     * @return $this
     */
    public function setEndUser($endUser)
    {
        $this->endUser = $endUser;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFreezeKeyPIN()
    {
        return $this->freezeKeyPIN;
    }

    /**
     * @param mixed $freezeKeyPIN
     *
     * @return $this
     */
    public function setFreezeKeyPIN($freezeKeyPIN)
    {
        $this->freezeKeyPIN = $freezeKeyPIN;

        return $this;
    }

    /**
     * @return string
     */
    public function getInquiryChannel()
    {
        return $this->inquiryChannel;
    }

    /**
     * @param string $inquiryChannel
     *
     * @return $this
     */
    public function setInquiryChannel($inquiryChannel)
    {
        $this->inquiryChannel = $inquiryChannel;

        return $this;
    }

    /**
     * @return string
     */
    public function getOfac()
    {
        return $this->ofac;
    }

    /**
     * @param string $ofac
     *
     * @return $this
     */
    public function setOfac($ofac)
    {
        $this->ofac = $ofac;

        return $this;
    }

    /**
     * @return string
     */
    public function getOfacmsg()
    {
        return $this->ofacmsg;
    }

    /**
     * @param string $ofacmsg
     *
     * @return $this
     */
    public function setOfacmsg($ofacmsg)
    {
        $this->ofacmsg = $ofacmsg;

        return $this;
    }

    /**
     * @return int
     */
    public function getPreciseIDType()
    {
        return $this->preciseIDType;
    }

    /**
     * @param int $preciseIDType
     *
     * @return $this
     */
    public function setPreciseIDType($preciseIDType)
    {
        $this->preciseIDType = $preciseIDType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReferenceNumber()
    {
        return $this->referenceNumber;
    }

    /**
     * @param mixed $referenceNumber
     *
     * @return $this
     */
    public function setReferenceNumber($referenceNumber)
    {
        $this->referenceNumber = $referenceNumber;

        return $this;
    }
}
