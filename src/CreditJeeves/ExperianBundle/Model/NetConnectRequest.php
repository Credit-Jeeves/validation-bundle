<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("NetConnectRequest")
 */
class NetConnectRequest
{
    /**
     * @Serializer\SerializedName("EAI")
     * @Serializer\Groups({"PreciseID"})
     * @var string
     */
    protected $eai;

    /**
     * @Serializer\SerializedName("DBHost")
     * @Serializer\Groups({"PreciseID"})
     * @var string
     */
    protected $dbHost;

    /**
     * @Serializer\SerializedName("ReferenceId")
     * @Serializer\Groups({"PreciseID"})
     * @var int
     */
    protected $referenceId = 123;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Request")
     * @Serializer\Groups({"PreciseID"})
     * @var Request
     */
    protected $request;

    /**
     * @return string
     */
    public function getEai()
    {
        return $this->eai;
    }

    /**
     * @param string $eai
     *
     * @return $this
     */
    public function setEai($eai)
    {
        $this->eai = $eai;

        return $this;
    }

    /**
     * @return string
     */
    public function getDbHost()
    {
        return $this->dbHost;
    }

    /**
     * @param string $dbHost
     *
     * @return $this
     */
    public function setDbHost($dbHost)
    {
        $this->dbHost = $dbHost;

        return $this;
    }

    /**
     * @return int
     */
    public function getReferenceId()
    {
        return $this->referenceId;
    }

    /**
     * @param int $referenceId
     *
     * @return $this
     */
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;

        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        if (null == $this->request) {
            $this->request = new Request();
        }
        return $this->request;
    }

    /**
     * @param Request $request
     *
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }
}
