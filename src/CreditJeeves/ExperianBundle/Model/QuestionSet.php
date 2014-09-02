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
     * @Serializer\Type("array<string>")
     * @var array
     */
    protected $questionSelect = array();

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
     * @return array
     */
    public function getQuestionSelect()
    {
        return $this->questionSelect;
    }

    /**
     * @param array $questionSelect
     *
     * @return $this
     */
    public function setQuestionSelect(array $questionSelect)
    {
        $this->questionSelect = $questionSelect;

        return $this;
    }
}
