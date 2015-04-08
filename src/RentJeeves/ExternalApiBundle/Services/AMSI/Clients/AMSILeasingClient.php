<?php

namespace RentJeeves\ExternalApiBundle\Services\AMSI\Clients;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use RentJeeves\ComponentBundle\Helper\SerializerXmlHelper;
use RentJeeves\ExternalApiBundle\Model\AMSI\EDEX;
use RentJeeves\ExternalApiBundle\Model\AMSI\PropertyResidents;
use RentJeeves\ExternalApiBundle\Model\AMSI\PropertyUnits;
use Exception;

class AMSILeasingClient extends AMSIBaseClient
{
    /**
     * @param $propertyId
     * @return array
     * @throws Exception
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
            $this->getDeserializationContext()
        );

        if ($propertyUnits instanceof PropertyUnits && count($propertyUnits->getUnits()) > 0) {
            return $propertyUnits->getUnits();
        }

        throw new Exception(sprintf("Don't have data, when deserialize AMSI response (%s)", $result));
    }

    /**
     * @param $propertyId
     * @return array
     */
    protected function getParametersForPropertyUnits($propertyId)
    {
        $edex = new EDEX();
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
                ['XMLData'=> new \SoapVar($xmlData, XSD_ANYXML)]
            ),
        ];

        return $parameters;
    }

    /**
     * @param string $propertyId
     * @param string $leaseStatus
     * @return PropertyResidents
     * @throws Exception
     */
    public function getPropertyResidents($propertyId, $leaseStatus)
    {
        $result = $this->sendRequest(
            'GetPropertyResidents',
            $this->getParametersForPropertyResidents($propertyId, $leaseStatus)
        );

        $result = SerializerXmlHelper::replaceEscapeToCorrectSymbol($result);
        /** @var PropertyResidents $propertyResidents */
        $propertyResidents = $this->serializer->deserialize(
            $result,
            'RentJeeves\ExternalApiBundle\Model\AMSI\PropertyResidents',
            'xml',
            $this->getDeserializationContext()
        );

        if ($propertyResidents instanceof PropertyResidents && count($propertyResidents->getLease()) > 0) {
            return $propertyResidents;
        }

        throw new Exception(sprintf("Don't have data, when deserialize AMSI response (%s)", $result));
    }

    /**
     * @param string $propertyId
     * @param string $leaseStatus
     * @return array
     */
    protected function getParametersForPropertyResidents($propertyId, $leaseStatus)
    {
        $edex = new EDEX();
        $edex->setPropertyId($propertyId);
        $edex->setLeaseStatus($leaseStatus);

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
                ['XMLData'=> new \SoapVar($xmlData, XSD_ANYXML)]
            ),
        ];

        return $parameters;
    }

    /**
     * @return array
     */
    protected function getLoginCredentials()
    {
        return [
            'UserID'  => $this->settings->getUser(),
            'Password'=> $this->settings->getPassword(),
            'PortfolioName' => $this->settings->getPortfolioName(),
        ];
    }

    /**
     * @param array $groups
     * @return SerializationContext
     */
    protected function getSerializationContext($groups)
    {
        $serializerContext = new SerializationContext();
        $serializerContext->setGroups($groups);

        return $serializerContext;
    }

    /**
     * @return DeserializationContext
     */
    protected function getDeserializationContext()
    {
        $deserializerContext = new DeserializationContext();
        $deserializerContext->setGroups(['AMSI']);

        return $deserializerContext;
    }
}
