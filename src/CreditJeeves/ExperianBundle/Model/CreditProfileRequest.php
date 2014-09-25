<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

trait CreditProfileRequest
{
    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Subscriber")
     * @Serializer\Groups({"CreditProfile"})
     * @var Subscriber
     */
    protected $subscriber;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\PrimaryApplicant")
     * @Serializer\Groups({"CreditProfile"})
     * @var PrimaryApplicant
     */
    protected $primaryApplicant;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\AccountType")
     * @Serializer\Groups({"CreditProfile"})
     * @var AccountType
     */
    protected $accountType;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\AddOns")
     * @Serializer\Groups({"CreditProfile"})
     * @var AddOns
     */
    protected $addOns;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\OutputType")
     * @Serializer\Groups({"CreditProfile"})
     * @var OutputType
     */
    protected $outputType;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Vendor")
     * @Serializer\Groups({"CreditProfile"})
     * @var Vendor
     */
    protected $vendor;

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
     *
     * @return $this
     */
    public function setSubscriber($subscriber)
    {
        $this->subscriber = $subscriber;

        return $this;
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
     *
     * @return $this
     */
    public function setPrimaryApplicant($primaryApplicant)
    {
        $this->primaryApplicant = $primaryApplicant;

        return $this;
    }

    /**
     * @return AccountType
     */
    public function getAccountType()
    {
        if (null == $this->accountType) {
            $this->accountType = new AccountType();
        }
        return $this->accountType;
    }

    /**
     * @param AccountType $accountType
     *
     * @return $this
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;

        return $this;
    }

    /**
     * @return AddOns
     */
    public function getAddOns()
    {
        if (null == $this->addOns) {
            $this->addOns = new AddOns();
        }
        return $this->addOns;
    }

    /**
     * @param AddOns $addOns
     *
     * @return $this
     */
    public function setAddOns($addOns)
    {
        $this->addOns = $addOns;

        return $this;
    }

    /**
     * @return OutputType
     */
    public function getOutputType()
    {
        if (null == $this->outputType) {
            $this->outputType = new OutputType();
        }
        return $this->outputType;
    }

    /**
     * @param OutputType $outputType
     *
     * @return $this
     */
    public function setOutputType($outputType)
    {
        $this->outputType = $outputType;

        return $this;
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
     *
     * @return $this
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;

        return $this;
    }
}
