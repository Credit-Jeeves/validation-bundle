<?php

namespace RentJeeves\TestBundle\Services\ResMan;

use RentJeeves\ExternalApiBundle\Model\ResMan\ResidentTransactions;
use RentJeeves\ExternalApiBundle\Services\ResMan\ResManClient as Base;

class ResManClient extends Base
{
    const TEST_EXTERNAL_PROPERTY_ID = 'test_resman_external_property_id';

    /** @var string File with response example */
    protected $responseMockFile = '/../../Resources/fixtures/ResMan-getResidentTransactions.xml';

    /**
     * @param string $externalPropertyId
     *
     * @return ResidentTransactions
     */
    public function getResidentTransactions($externalPropertyId)
    {
        if ($externalPropertyId === self::TEST_EXTERNAL_PROPERTY_ID) {
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
            realpath(dirname(__FILE__)).$this->responseMockFile
        );
    }

    /**
     * Method for setting response file with empty move out date
     */
    public function setResponseMockFileWithEmptyMoveOut()
    {
        $this->responseMockFile = '/../../Resources/fixtures/ResMan-getResidentTransactions(empty_move_out).xml';
    }
}
