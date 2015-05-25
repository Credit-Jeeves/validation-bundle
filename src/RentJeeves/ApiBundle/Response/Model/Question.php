<?php

namespace RentJeeves\ApiBundle\Response\Model;

use JMS\Serializer\Annotation as Serializer;

class Question
{
    /**
     * @var int
     * @Serializer\Groups({"IdentityVerificationDetails"})
     */
    public $id;

    /**
     * @var string
     * @Serializer\Groups({"IdentityVerificationDetails"})
     */
    public $question;

    /**
     * @var array
     * @Serializer\Type("array<RentJeeves\ApiBundle\Response\Model\Choice>")
     * @Serializer\Groups({"IdentityVerificationDetails"})
     */
    public $choices = [];
}
