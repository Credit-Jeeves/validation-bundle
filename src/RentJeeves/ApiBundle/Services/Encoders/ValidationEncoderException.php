<?php

namespace RentJeeves\ApiBundle\Services\Encoders;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ValidationEncoderException extends EncoderException implements HttpExceptionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return 400;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return [];
    }
}
