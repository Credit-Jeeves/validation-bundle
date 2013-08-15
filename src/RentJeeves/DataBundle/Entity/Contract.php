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
        $result['id'] = $this->getId();
        $result['status'] = $status['status'];
        $result['style'] = $status['class'];
        $result['address'] = $this->getRentAddress($property, $unit);
        $result['property_id'] = $property->getId();
        $result['unit_id'] = null;
        if ($unit) {
            $result['unit_id'] = $unit->getId();
        }
        $result['tenant'] = ucfirst(strtolower($tenant->getFullName()));
        $result['first_name'] = $tenant->getFirstName();
        $result['last_name'] = $tenant->getLastName();
        $result['email'] = $tenant->getEmail();
        $result['phone'] = $tenant->getFomattedPhone();
        $result['amount'] = 'undefined';
        if ($rent = $this->getRent()) {
            $result['amount'] = '$'.$this->getRent();
        }
        $result['due_day'] = $this->getDueDay().'th';
        $result['paid_to'] = '';
        if ($date = $this->getPaidTo()) {
            $result['paid_to'] = $date->format('M d, Y');
        }
        $result['start'] = '';
        if ($start = $this->getStartAt()) {
            $result['start'] = $start->format('m/d/Y');
        }
        $result['finish'] = '';
        if ($finish = $this->getFinishAt()) {
            $result['finish'] = $finish->format('m/d/Y');
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
            $result['status'] = 'CURRENT';
            $result['class'] = '';
            return $result;
        }
        $result['status'] = strtoupper($this->getStatus());
        $result['class'] = '';
        return $result;
    }

    public function getRentAddress($property, $unit)
    {
        $result = array();
        $result[] = $property->getAddress();
        if ($unit) {
            $result[] = $unit->getName();
        }
        return implode(', #', $result);
    }

    public function getActivePaymentHistory()
    {
        return $this->getPaymentHistory();
    }

    public function getFinishedPaymentHistory()
    {
        return $this->getPaymentHistory();
    }

    public function getPaymentHistory()
    {
        $result = array('history' => array(), 'last_amount' => 0, 'last_date' => '');
        $payments = array();
        $currentDate = new \DateTime('now');
        $lastDate = $currentDate->diff($this->getCreatedAt())->format('%r%a');
        $operations = $this->getOperations();
        foreach ($operations as $operation) {
            $orders = $operation->getOrders();
            foreach ($orders as $order) {
                $orderDate = $order->getCreatedAt();
                $interval = $currentDate->diff($orderDate)->format('%r%a');
                if ($interval > $lastDate) {
                    $result['last_amount'] = $order->getAmount();
                    $result['last_date'] = $order->getCreatedAt()->format('m/d/Y');
                }
                $nYear = $order->getCreatedAt()->format('Y');
                $nMonth = $order->getCreatedAt()->format('m');
                if (!isset($payments[$nYear][$nMonth])) {
                    $payments[$nYear][$nMonth] = array('status' => 'C', 'text' => 'OK');
                }
            }
        }
        $result['history'] = $payments;
//         echo '<pre>';
//         print_r($result);
//         echo '</pre>';
        return $result;
    }
}
