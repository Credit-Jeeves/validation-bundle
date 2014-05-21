<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\ContractWaiting as Base;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Contract
 *
 * @ORM\Entity
 * @ORM\Table(name="rj_contract_waiting")
 */
class ContractWaiting extends Base
{
    /**
     * @return array
     */
    public function getImportDataForFind()
    {
        //Documentation about field: https://credit.atlassian.net/wiki/display/RT/Tenant+Waiting+Room
        $data = array(
            'residentId'    => $this->getResidentId(),
            'firstName'     => $this->getFirstName(),
            'lastName'      => $this->getLastName(),
            'property'      => $this->getProperty()->getId(),
        );

        $unit = $this->getUnit();

        if (!empty($unit) && !$this->getProperty()->isSingle()) {
            $data['unit'] = $unit->getId();
        } else {
            $data['unit'] = null;
        }

        return $data;
    }
}
