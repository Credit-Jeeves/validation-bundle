<?php

namespace RentJeeves\ApiBundle\Response;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\ApiBundle\Response\Model\Choice;
use RentJeeves\ApiBundle\Response\Model\Question;
use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation\UrlResourceMeta;
use CreditJeeves\DataBundle\Entity\Pidkiq as Entity;
use RentJeeves\ComponentBundle\PidKiqProcessor\PidKiqMessageGenerator;

/**
 * @DI\Service("response_resource.identity_verification")
 * @UrlResourceMeta(
 *      actionName = "get_identity_verification"
 * )
 */
class Pidkiq extends ResponseResource
{
    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var PidKiqMessageGenerator
     *
     * @DI\Inject("pidkiq.message_generator", required=true)
     */
    public $pidKiqMessageGenerator;

    /**
     * @var string
     * @DI\Inject("%pidkiq.lifetime.minutes%")
     */
    public $lifetime;

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"IdentityVerificationDetails"})
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->entity->getStatus();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"IdentityVerificationDetails"})
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->pidKiqMessageGenerator->generateMessage(
            $this->entity->getStatus()
        );
    }

    /**
     * @Serializer\Type("array")
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"IdentityVerificationDetails"})
     *
     * @return array
     */
    public function getQuestions()
    {
        return $this->prepareQuestions(
            $this->entity->getQuestions() ?: []
        );
    }

    /**
     * @param array $questions
     * @return array
     */
    protected function prepareQuestions(array $questions)
    {
        $questionId = 1;
        $preparedQuestions = [];
        foreach ($questions as $question => $choices) {
            $preparedQuestion = new Question();
            $preparedQuestion->id = $questionId++;
            $preparedQuestion->question = $question;

            $choiceId = 1;
            $preparedChoices = [];
            foreach ($choices as $choice) {
                $preparedChoice = new Choice();
                $preparedChoice->id = $choiceId++;
                $preparedChoice->choice = $choice;

                $preparedChoices[] = $preparedChoice;
            }
            $preparedQuestion->choices = $preparedChoices;

            $preparedQuestions[] = $preparedQuestion;
        }

        return $preparedQuestions;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"IdentityVerificationDetails"})
     *
     * @return string
     */
    public function getExpires()
    {
        return $this->entity->getCreatedAt()->modify('+' . (int) $this->lifetime . ' minutes')->format('U');
    }
}
