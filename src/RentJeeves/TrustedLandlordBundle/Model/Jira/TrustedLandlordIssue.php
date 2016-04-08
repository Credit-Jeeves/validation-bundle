<?php

namespace RentJeeves\TrustedLandlordBundle\Model\Jira;

use JMS\Serializer\Annotation as Serializer;

class TrustedLandlordIssue
{
    /**
     * @Serializer\Type("RentJeeves\TrustedLandlordBundle\Model\Jira\Transition")
     *
     * @var Transition
     */
    protected $transition;

    /**
     * @Serializer\Type("RentJeeves\TrustedLandlordBundle\Model\Jira\Issue")
     *
     * @var Issue
     */
    protected $issue;

    /**
     * @return Issue
     */
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * @param Issue $issue
     */
    public function setIssue(Issue $issue)
    {
        $this->issue = $issue;
    }

    /**
     * @return Transition
     */
    public function getTransition()
    {
        return $this->transition;
    }

    /**
     * @param Transition $transition
     */
    public function setTransition(Transition $transition)
    {
        $this->transition = $transition;
    }
}
