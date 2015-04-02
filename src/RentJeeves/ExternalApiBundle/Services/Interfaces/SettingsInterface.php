<?php

namespace RentJeeves\ExternalApiBundle\Services\Interfaces;

/**
 * This interface used for return parameters to api client
 */
interface SettingsInterface
{
    /**
     * @return array
     */
    public function getParameters();
}
