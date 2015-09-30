<?php

namespace RentJeeves\ExternalApiBundle\Services\AMSI\Clients;

use RentJeeves\ComponentBundle\Helper\SerializerXmlHelper;
use RentJeeves\ExternalApiBundle\Model\AMSI\EdexResidents;
use RentJeeves\ExternalApiBundle\Model\AMSI\InternetTrafficResponse;
use RentJeeves\ExternalApiBundle\Model\AMSI\PropertyResidents;
use RentJeeves\ExternalApiBundle\Model\AMSI\PropertyUnits;
use RentJeeves\ExternalApiBundle\Model\AMSI\Lease;

class AMSILeasingClient extends AMSIBaseClient
{
    /**
     * @param  integer    $propertyId
     * @return array
     * @throws \Exception
     */
    public function getPropertyUnits($propertyId)
    {
        $result = $this->sendRequest(
            'GetPropertyUnits',
            $this->getParametersForPropertyUnits($propertyId)
        );

        $result = SerializerXmlHelper::replaceEscapeToCorrectSymbol($result);
        /** @var PropertyUnits $propertyUnits */
        $propertyUnits = $this->serializer->deserialize(
            $result,
            'RentJeeves\ExternalApiBundle\Model\AMSI\PropertyUnits',
            'xml',
            $this->getDeserializationContext(['AMSI'])
        );

        if ($propertyUnits instanceof PropertyUnits && count($propertyUnits->getUnits()) > 0) {
            return $propertyUnits->getUnits();
        }

        throw new \Exception(sprintf("Don't have data, when deserialize AMSI response (%s)", $result));
    }

    /**
     * @param  string            $propertyId
     * @param  string            $leaseStatus
     * @param  boolean           $includeRecurringCharges
     * @return PropertyResidents
     * @throws \Exception
     */
    public function getPropertyResidents($propertyId, $leaseStatus, $includeRecurringCharges = false)
    {
        $result = $this->sendRequest(
            'GetPropertyResidents',
            $this->getParametersForPropertyResidents($propertyId, $leaseStatus, $includeRecurringCharges)
        );

        $result = SerializerXmlHelper::replaceEscapeToCorrectSymbol($result);
        /** @var PropertyResidents $propertyResidents */
        $propertyResidents = $this->serializer->deserialize(
            $result,
            'RentJeeves\ExternalApiBundle\Model\AMSI\PropertyResidents',
            'xml',
            $this->getDeserializationContext(['AMSI'])
        );

        if ($propertyResidents instanceof PropertyResidents && count($propertyResidents->getLeases()) > 0) {
            return $propertyResidents;
        }
        /** @var InternetTrafficResponse $internetTrafficResponse*/
        $internetTrafficResponse = $this->serializer->deserialize(
            $result,
            'RentJeeves\ExternalApiBundle\Model\AMSI\InternetTrafficResponse',
            'xml',
            $this->getDeserializationContext(['AMSI'])
        );

        if ($internetTrafficResponse instanceof InternetTrafficResponse) {
            if ($leaseStatus == Lease::STATUS_CURRENT) {
                $this->logger->alert(
                    sprintf(
                        'AMSI can\'t list CURRENT residents for property ID (%s): %s',
                        $propertyId,
                        $internetTrafficResponse->getError()->getErrorDescription()
                    )
                );
            } else {
                $this->logger->info(
                    sprintf(
                        'AMSI can\'t list non-CURRENT residents for property ID (%s): %s',
                        $propertyId,
                        $internetTrafficResponse->getError()->getErrorDescription()
                    )
                );
            }

            return new PropertyResidents();
        }

        throw new \Exception(sprintf('Unknown AMSI response:%s', $result));
    }

    /**
     * @param  integer $propertyId
     * @return array
     */
    protected function getParametersForPropertyUnits($propertyId)
    {
        $edex = new EdexResidents();
        $edex->setPropertyId($propertyId);

        $xmlData = SerializerXmlHelper::removeStandartHeaderXml(
            $this->serializer->serialize(
                $edex,
                'xml',
                $this->getSerializationContext(['GetPropertyUnits'])
            )
        );
        $xmlData = SerializerXmlHelper::addCDataToString($xmlData);
        $xmlData = SerializerXmlHelper::addTagWithNameSpaceToString('XMLData', 'ns1', $xmlData);

        $parameters = [
            'GetPropertyUnits' => array_merge(
                $this->getLoginCredentials(),
                ['XMLData' => new \SoapVar($xmlData, XSD_ANYXML)]
            ),
        ];

        return $parameters;
    }

    /**
     * @param  string $propertyId
     * @param  string $leaseStatus
     * @param  boolean $includeRecurringCharges
     *
     * @return array
     */
    protected function getParametersForPropertyResidents($propertyId, $leaseStatus, $includeRecurringCharges = false)
    {
        $edex = new EdexResidents();
        $edex->setPropertyId($propertyId);
        $edex->setLeaseStatus($leaseStatus);
        $edex->setIncludeRecurringCharges((int) $includeRecurringCharges);

        $xmlData = SerializerXmlHelper::removeStandartHeaderXml(
            $this->serializer->serialize(
                $edex,
                'xml',
                $this->getSerializationContext(['GetPropertyResidents'])
            )
        );
        $xmlData = SerializerXmlHelper::addCDataToString($xmlData);
        $xmlData = SerializerXmlHelper::addTagWithNameSpaceToString('XMLData', 'ns1', $xmlData);

        $parameters = [
            'GetPropertyResidents' => array_merge(
                $this->getLoginCredentials(),
                ['XMLData' => new \SoapVar($xmlData, XSD_ANYXML)]
            ),
        ];

        return $parameters;
    }
}
