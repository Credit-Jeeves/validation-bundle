<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("QuestionSet")
 */
class QuestionSet
{
    /**
     * @Serializer\SerializedName("QuestionText")
     * @Serializer\Type("string")
     * @var string
     */
    protected $questionText;

    /**
     * @Serializer\SerializedName("QuestionSelect")
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\QuestionSelect")
     * @var QuestionSelect
     */
    protected $questionSelect;

    /**
     * @return string
     */
    public function getQuestionText()
    {
        return $this->questionText;
    }

    /**
     * @param string $questionText
     *
     * @return $this
     */
    public function setQuestionText($questionText)
    {
        $this->questionText = $questionText;

        return $this;
    }

    /**
     * @return QuestionSelect
     */
    public function getQuestionSelect()
    {
        if (null == $this->questionSelect) {
            $this->questionSelect = new QuestionSelect();
        }
        return $this->questionSelect;
    }

    /**
     * @param QuestionSelect $questionSelect
     *
     * @return $this
     */
    public function setQuestionSelect($questionSelect)
    {
        $this->questionSelect = $questionSelect;

        return $this;
    }

    /**
     * @return array
     */
    public function getQuestionChoices()
    {
        return $this->getQuestionSelect()->getQuestionChoice();
    }
}
