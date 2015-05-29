<?php

namespace RentJeeves\ApiBundle\Response\Model;

use JMS\Serializer\Annotation as Serializer;

class Question
{
    /**
     * @var int
     * @Serializer\Groups({"IdentityVerificationDetails"})
     */
    protected $id;

    /**
     * @var string
     * @Serializer\Groups({"IdentityVerificationDetails"})
     */
    protected $question;

    /**
     * @var array<Choice>
     * @Serializer\Type("array<RentJeeves\ApiBundle\Response\Model\Choice>")
     * @Serializer\Groups({"IdentityVerificationDetails"})
     */
    protected $choices = [];

    /**
     * @param int $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $question
     * @return self
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param array<Choice> $choices
     * @return self
     */
    public function setChoices(array $choices)
    {
        $this->choices = $choices;

        return $this;
    }

    /**
     * @return array<Choice>
     */
    public function getChoices()
    {
        return $this->choices;
    }
}
