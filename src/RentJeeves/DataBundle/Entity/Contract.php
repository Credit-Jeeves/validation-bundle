<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\Contract as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contract
 *
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\ContractRepository")
 * @ORM\Table(name="rj_contract")
 */
class Contract extends Base
{
    public function getItem()
    {
        $result = array();
        $property = $this->getProperty();
        $tenant = $this->getTenant();
        $unit = $this->getUnit();
        $result['status'] = strtoupper($this->getStatus());
        $result['address'] = $property->getAddress();
        $result['unit'] = $unit->getName();
        $result['tenant'] = $tenant->getFullName();
        $result['amount'] = '$'.$this->getRent();
        $result['due_day'] = $this->getDueDay().'th';
        $result['paid_to'] = '';
        if ($date = $this->getPaidTo()) {
            $result['paid_to'] = $date->format('M d, Y');
        }
        return $result;
    }
}
