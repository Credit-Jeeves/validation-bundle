<?php

namespace RentJeeves\ApiBundle\Response\Model;

use JMS\Serializer\Annotation as Serializer;

class Choice
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
    public $choice;
}
