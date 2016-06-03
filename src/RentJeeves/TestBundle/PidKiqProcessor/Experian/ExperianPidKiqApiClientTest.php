<?php

namespace RentJeeves\TestBundle\PidKiqProcessor\Experian;

use CreditJeeves\ExperianBundle\Model\NetConnectRequest;
use RentJeeves\ComponentBundle\PidKiqProcessor\Experian\ExperianPidKiqApiClient;

class ExperianPidKiqApiClientTest extends ExperianPidKiqApiClient
{
    const TEST_SESSION_ID = '1BF7168380E8DB40CA9BE5D14F32F347.pidd1v-1408261641330210446354688';

    /**
     * {@inheritdoc}
     * just mock result for TEST SESSION
     */
    public function getResult(NetConnectRequest $request)
    {
        $sessionId = $request
            ->getRequest()
            ->getProducts()
            ->getPreciseIDServer()
            ->getKbaAnswers()
            ->getOutWalletAnswerData()
            ->getSessionID();

        if (self::TEST_SESSION_ID == $sessionId) {
            $requestXml = $this->prepareRequest($request, 'PreciseIDQuestions');

            $rightRequestXml = file_get_contents(
                $this->kernel->locateResource(
                    '@RjComponentBundle/Tests/Data/PidkiqProcessor/ExperianProcessAnswers-Request.xml'
                )
            );

            if ($requestXml == $rightRequestXml) {
                $responseXml = file_get_contents(
                    $this->kernel->locateResource(
                        '@RjComponentBundle/Tests/Data/PidkiqProcessor/ExperianProcessAnswers-Response.xml'
                    )
                );
            } else {
                $responseXml = file_get_contents(
                    $this->kernel->locateResource(
                        '@RjComponentBundle/Tests/Data/PidkiqProcessor/ExperianProcessAnswers-Response-Wrong.xml'
                    )
                );
            }

            return $this->prepareResponse($responseXml);
        } else {
            return parent::getResult($request);
        }
    }
}
