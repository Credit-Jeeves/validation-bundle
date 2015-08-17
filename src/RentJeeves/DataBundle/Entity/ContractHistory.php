<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\ContractHistory as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="rj_contract_history")
 * @ORM\Entity
 */
class ContractHistory extends Base
{
    /**
     * {@inheritdoc}
     */
    public function setData($data)
    {
        if (isset($data['status'])) {
            $this->setStatus($data['status']);
        }
        if (isset($data['rent'])) {
            $this->setRent($data['rent']);
        }
        if (isset($data['uncollectedBalance'])) {
            $this->setUncollectedBalance($data['uncollectedBalance']);
        }
        if (isset($data['integratedBalance'])) {
            $this->setIntegratedBalance($data['integratedBalance']);
        }
        if (isset($data['paidTo'])) {
            $this->setPaidTo($data['paidTo']);
        }
        if (isset($data['reporting'])) {
            $this->setReporting($data['reporting']);
        }
        if (isset($data['startAt'])) {
            $this->setStartAt($data['startAt']);
        }
        if (isset($data['finishAt'])) {
            $this->setFinishAt($data['finishAt']);
        }
    }
}
