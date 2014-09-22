<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("AddOns")
 */
class AddOns
{
    /**
     * @Serializer\SerializedName("ProfileSummary")
     * @Serializer\Type("string")
     * @Serializer\Groups({"CreditProfile"})
     * @var string
     */
    protected $profileSummary = 'Y';

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\RiskModels")
     * @Serializer\Groups({"CreditProfile"})
     * @var RiskModels
     */
    protected $riskModels;

    public function __construct()
    {
        $this->getRiskModels();
    }

    /**
     * @return string
     */
    public function getProfileSummary()
    {
        return $this->profileSummary;
    }

    /**
     * @param string $profileSummary
     *
     * @return $this
     */
    public function setProfileSummary($profileSummary)
    {
        $this->profileSummary = $profileSummary;

        return $this;
    }

    /**
     * @return RiskModels
     */
    public function getRiskModels()
    {
        if (null == $this->riskModels) {
            $this->riskModels = new RiskModels();
        }
        return $this->riskModels;
    }

    /**
     * @param RiskModels $riskModels
     *
     * @return $this
     */
    public function setRiskModels($riskModels)
    {
        $this->riskModels = $riskModels;

        return $this;
    }
}
