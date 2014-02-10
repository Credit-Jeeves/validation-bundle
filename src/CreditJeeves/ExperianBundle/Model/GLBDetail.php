<?php

namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("GLBDetail")
 */
class GLBDetail
{
    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\SharedApplication")
     * @Serializer\SerializedName("SharedApplication")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $sharedApplication;

    /**
     * @param SharedApplication $sharedApplication
     */
    public function setSharedApplication($sharedApplication)
    {
        $this->sharedApplication = $sharedApplication;
    }

    /**
     * @return SharedApplication
     */
    public function getSharedApplication()
    {
        if (empty($this->sharedApplication)) {
            $this->sharedApplication = new SharedApplication();
        }
        return $this->sharedApplication;
    }
}
