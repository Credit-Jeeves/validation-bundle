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
        //@TODO ask Darryl about field which use for waiting room
        return array(
            'unit'          => $this->getUnit()->getId(),
            'residentId'    => $this->getResidentId(),
            'firstName'     => $this->getFirstName(),
            'lastName'      => $this->getLastName()
        );
    }
}
