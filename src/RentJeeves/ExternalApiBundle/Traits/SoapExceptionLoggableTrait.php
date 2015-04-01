<?php

namespace RentJeeves\ExternalApiBundle\Traits;

trait SoapExceptionLoggableTrait
{
    /**
     * @param \Exception $e
     */
    protected function exceptionLog(\Exception $e)
    {
        if ($this->logger) {
            $this->logger->addCritical(
                sprintf(
                    'Exception on Yardi message(%s), file(%s), line(%s), request(%s) header(%s)',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine(),
                    $this->soapClient->__getLastRequest(),
                    $this->soapClient->__getLastRequestHeaders()
                )
            );
        }

        if ($this->exceptionCatcher) {
            $this->exceptionCatcher->handleException($e);
        }
    }
}
