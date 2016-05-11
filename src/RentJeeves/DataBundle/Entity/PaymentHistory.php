<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\PaymentHistory as BasePaymentHistory;

/**
 * @ORM\Entity
 * @ORM\Table(name="rj_payment_history")
 */
class PaymentHistory extends BasePaymentHistory
{
    /**
     * @var array
     */
    protected $loggedFields = [
        'contract', 'paymentAccount', 'depositAccount', 'type', 'status', 'amount', 'total', 'paidFor',
        'dueDate', 'startMonth', 'startYear', 'endMonth', 'endYear', 'updatedAt', 'closeDetails'
    ];

    /**
     * @var array
     */
    protected $relations = ['contract', 'paymentAccount', 'depositAccount'];

    /**
     * {@inheritdoc}
     */
    public function setData($data)
    {
        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (true === in_array($key, $this->relations)) { // For relation with another Entities
                $value = isset($value['id']) ? $value['id'] : $value[1];
                $method .= 'Id';
            }
            if (true === in_array($key, $this->loggedFields) && true === method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getId();
    }
}
