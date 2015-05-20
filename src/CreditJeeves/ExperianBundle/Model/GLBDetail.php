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
     *
     * @var SharedApplication
     */
    protected $sharedApplication;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\CheckpointSummary")
     * @Serializer\SerializedName("CheckpointSummary")
     * @Serializer\Groups({"CreditJeeves"})
     *
     * @var CheckpointSummary
     */
    protected $checkpointSummary;

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

    /**
     * @param CheckpointSummary $checkpointSummary
     */
    public function setCheckpointSummary(CheckpointSummary $checkpointSummary)
    {
        $this->checkpointSummary = $checkpointSummary;
    }

    /**
     * @return CheckpointSummary
     */
    public function getCheckpointSummary()
    {
        if (empty($this->checkpointSummary)) {
            $this->checkpointSummary = new CheckpointSummary();
        }

        return $this->checkpointSummary;
    }
}
