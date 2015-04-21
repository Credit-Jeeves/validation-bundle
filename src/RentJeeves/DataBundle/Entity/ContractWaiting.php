<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\ContractWaiting as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contract
 *
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\ContractWaitingRepository")
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
        return array(
            'residentId'    => $this->getResidentId(),
            'property'      => $this->getProperty()->getId(),
            'unit'          => $this->getUnit()->getId(),
            'group'         => $this->getGroup()
        );
    }
}
