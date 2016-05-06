<?php

namespace RentJeeves\TestBundle\Services\ResMan;

use RentJeeves\ExternalApiBundle\Model\ResMan\ResidentTransactions;
use RentJeeves\ExternalApiBundle\Services\ResMan\ResManClient as Base;

class ResManClient extends Base
{
    /**
     * @param string $externalPropertyId
     *
     * @return ResidentTransactions
     */
    public function getResidentTransactions($externalPropertyId)
    {
        if ($externalPropertyId === 'test_resman_external_property_id') {
            $resMan = $this->deserializeResponse($this->getResponseMock(), $this->mappingResponse[self::BASE_RESPONSE]);

            return $resMan->getResponse()->getResidentTransactions();
        } else {
            return parent::getResidentTransactions($externalPropertyId);
        }
    }

    /**
     * @return string XML data from Resman
     */
    protected function getResponseMock()
    {
        return file_get_contents(
            realpath(dirname(__FILE__)) . '/../../Resources/fixtures/ResMan-getResidentTransactions.xml'
        );
    }
}

