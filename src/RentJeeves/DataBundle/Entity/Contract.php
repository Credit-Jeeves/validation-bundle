<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\Contract as Base;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\ContractStatus;

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
        $status = $this->getStatusArray();
        $result['status'] = $status['status'];
        $result['style'] = $status['class'];
        $result['address'] = $property->getAddress();
        $result['unit'] = 'Not selected';
        if ($unit) {
            $result['unit'] = $unit->getName();
        }
        $result['tenant'] = $tenant->getFullName();
        $result['amount'] = 'undefined';
        if ($rent = $this->getRent()) {
            $result['amount'] = '$'.$this->getRent();
        }
        $result['due_day'] = $this->getDueDay().'th';
        $result['paid_to'] = '';
        if ($date = $this->getPaidTo()) {
            $result['paid_to'] = $date->format('M d, Y');
        }
        return $result;
    }

    public function getStatusArray()
    {
        $result = array('status' => strtoupper(ContractStatus::PENDING), 'class' => 'contract-pending');
        if (ContractStatus::PENDING == $this->getStatus()) {
            return $result;
        }
        if (ContractStatus::FINISHED == $this->getStatus()) {
            $result['status'] = strtoupper(ContractStatus::FINISHED);
            $result['class'] = '';
            return $result;
        }
        if ($date = $this->getPaidTo()) {
            $now = new \DateTime();
            $interval = $now->diff($date);
            if ($sign = $interval->format('%r')) {
                $days = $interval->format('%d');
                $result['status'] = 'LATE ('.$days.' days)';
                $result['class'] = 'contract-late';
                return $result;
            }
            $result['status'] = 'PAID';
            $result['class'] = '';
            return $result;
        }
        $result['status'] = strtoupper($this->getStatus());
        $result['class'] = '';
        return $result;
    }
}
