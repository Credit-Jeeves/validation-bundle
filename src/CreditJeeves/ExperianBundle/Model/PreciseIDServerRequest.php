<?php

namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

trait PreciseIDServerRequest
{
    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Subscriber")
     * @Serializer\Groups({"PreciseID"})
     * @var Subscriber
     */
    protected $subscriber;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\PrimaryApplicant")
     * @Serializer\Groups({"PreciseID"})
     * @var PrimaryApplicant
     */
    protected $primaryApplicant;

    /**
     * @Serializer\SerializedName("Verbose")
     * @Serializer\Type("string")
     * @Serializer\Groups({"PreciseID"})
     * @var string
     */
    protected $verbose = 'Y';

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Vendor")
     * @Serializer\Groups({"PreciseID"})
     * @var Vendor
     */
    protected $vendor;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Options")
     * @Serializer\Groups({"PreciseID"})
     * @var Options
     */
    protected $options;

    /**
     * @Serializer\SerializedName("IPAddress")
     * @Serializer\Type("string")
     * @Serializer\Groups({"PreciseID"})
     * @var string
     */
    protected $ipAddress;

    /**
     * @return Subscriber
     */
    public function getSubscriber()
    {
        if (null == $this->subscriber) {
            $this->subscriber = new Subscriber();
        }

        return $this->subscriber;
    }

    /**
     * @param Subscriber $subscriber
     */
    public function setSubscriber(Subscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * @return PrimaryApplicant
     */
    public function getPrimaryApplicant()
    {
        if (null == $this->primaryApplicant) {
            $this->primaryApplicant = new PrimaryApplicant();
        }

        return $this->primaryApplicant;
    }

    /**
     * @param PrimaryApplicant $primaryApplicant
     */
    public function setPrimaryApplicant(PrimaryApplicant $primaryApplicant)
    {
        $this->primaryApplicant = $primaryApplicant;
    }

    /**
     * @return string
     */
    public function getVerbose()
    {
        return $this->verbose;
    }

    /**
     * @param string $verbose
     */
    public function setVerbose($verbose)
    {
        $this->verbose = $verbose;
    }

    /**
     * @return Vendor
     */
    public function getVendor()
    {
        if (null == $this->vendor) {
            $this->vendor = new Vendor();
        }

        return $this->vendor;
    }

    /**
     * @param Vendor $vendor
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;
    }

    /**
     * @return Options
     */
    public function getOptions()
    {
        if (null == $this->options) {
            $this->options = new Options();
        }

        return $this->options;
    }

    /**
     * @param Options $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }
}
