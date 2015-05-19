<?php

namespace RentJeeves\ComponentBundle\PidKiqProcessor;

interface PidKiqStateAwareInterface
{
    /**
     * @return bool
     */
    public function getIsSuccessfull();

    /**
     * @return string
     */
    public function getMessage();
}
