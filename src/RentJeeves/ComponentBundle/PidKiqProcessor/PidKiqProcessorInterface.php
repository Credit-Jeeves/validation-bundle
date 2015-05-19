<?php

namespace RentJeeves\ComponentBundle\PidKiqProcessor;

use CreditJeeves\DataBundle\Entity\Pidkiq;
use CreditJeeves\DataBundle\Entity\User;

interface PidKiqProcessorInterface
{
    /**
     * Retrieve questions from service or from DB cache
     *
     * @return array
     */
    public function retrieveQuestions();

    /**
     * @param array $answers
     * @return bool
     */
    public function processAnswers(array $answers);

    /**
     * @return Pidkiq
     */
    public function getPidkiqModel();

    /**
     * @param User $user
     */
    public function setUser(User $user);
}
