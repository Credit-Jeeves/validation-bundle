<?php

namespace RentJeeves\ComponentBundle\PidKiqProcessor;

use CreditJeeves\DataBundle\Entity\User;
use RentJeeves\ComponentBundle\PidKiqProcessor\PidKiqProcessorInterface as PidKiqProcessor;

class PidKiqProcessorFactory
{
    /**
     * @var PidKiqProcessorExperian
     */
    protected $experian;

    /**
     * @param PidKiqProcessor $experian
     */
    public function setPidKiqProcessors(PidKiqProcessor $experian)
    {
        $this->experian = $experian;
    }

    /**
     * Returns a Precise ID and Knowledge IQ processor.
     *
     * @param  User            $user
     * @return PidKiqProcessor
     */
    public function getPidKiqProcessor(User $user = null)
    {
        if ($user) {
            $this->experian->setUser($user);
        }

        return $this->experian;
    }
}
