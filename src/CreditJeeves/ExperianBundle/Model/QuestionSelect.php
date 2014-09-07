<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("QuestionSelect")
 */
class QuestionSelect
{
    /**
     * @Serializer\XmlMap(inline = true, entry = "QuestionChoice")
     * @Serializer\Type("array<string>")
     * @var array
     */
    protected $questionChoice = array();

    /**
     * @return array
     */
    public function getQuestionChoice()
    {
        return $this->questionChoice;
    }

    /**
     * @param array $questionChoice
     *
     * @return $this
     */
    public function setQuestionChoice($questionChoice)
    {
        $this->questionChoice = $questionChoice;

        return $this;
    }
}
