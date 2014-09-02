<?php

namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\ExperianBundle\Model\InitialResults;

/**
 * @Serializer\XmlRoot("KBA")
 */
class KBA extends KBAScore
{
    /**
     * @Serializer\Type("array<CreditJeeves\ExperianBundle\Model\QuestionSet>")
     * @Serializer\SerializedName("QuestionSet")
     * @var array
     */
    protected $questionSet = array();

    /**
     * @return array
     */
    public function getQuestionSet()
    {
        return $this->questionSet;
    }

    /**
     * @param array $questionSet
     *
     * @return $this
     */
    public function setQuestionSet(array $questionSet)
    {
        $this->questionSet = $questionSet;

        return $this;
    }
}
