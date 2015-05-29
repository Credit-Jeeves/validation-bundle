<?php

namespace RentJeeves\ApiBundle\Response\Model;

use JMS\Serializer\Annotation as Serializer;

class Choice
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
    protected $choice;

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
     * @param string $choice
     * @return self
     */
    public function setChoice($choice)
    {
        $this->choice = $choice;

        return $this;
    }

    /**
     * @return string
     */
    public function getChoice()
    {
        return $this->choice;
    }
}
