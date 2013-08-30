<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\Contract as Base;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\ContractStatus;
use CreditJeeves\DataBundle\Enum\OrderStatus;

/**
 * Contract
 *
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\ContractRepository")
 * @ORM\Table(name="rj_contract")
 */
class Contract extends Base
{
    /**
     * @var string
     */
    const RESOLVE_EMAIL = 'email';

    /**
     * @var string
     */
    const RESOLVE_PAID = 'paid';

    /**
     * @var string
     */
    const RESOLVE_UNPAID = 'unpaid';

    /**
     * @var string
     */
    const PAYMENT_OK = 'OK';

    /**
     * @var string
     */
    const PAYMENT_PAY = 'PAY';

    /**
     * @var string
     */
    const PAYMENT_AUTO = 'AUTO';

    /**
     * @var string
     */
    const PAYMENT_LATE = ' DAYS LATE';

    const STATUS_EMPTY = 'empty-month';

    const STATUS_PAY = 'auto';

    const STATUS_OK = 'C';

    const STATUS_LATE = '1';

    /**
     * @return array
     */
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
        $result['full_address'] = $this->getRentAddress($property, $unit).' '.$property->getLocationAddress();
        $result['property_id'] = $property->getId();
        $result['unit_id'] = null;
        if ($unit) {
            $result['unit_id'] = $unit->getId();
        }
        $result['tenant'] = ucwords(strtolower($tenant->getFullName()));
        $result['first_name'] = $tenant->getFirstName();
        $result['last_name'] = $tenant->getLastName();
        $result['email'] = $tenant->getEmail();
        $result['phone'] = $tenant->getFomattedPhone();
        $result['amount'] = 'undefined';
        if ($rent = $this->getRent()) {
            $result['amount'] = $this->getRent();
        }
        $result['due_day'] = $this->getDueDay().'th';
        $result['late'] = $this->getLateDays();
        $result['paid_to'] = '';
        $result['late_date'] = '';
        if ($date = $this->getPaidTo()) {
            $result['paid_to'] = $date->format('M d, Y');
            $result['late_date'] = $date->format('n/j/Y');
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

    private function getLateDays()
    {
        $result = '1 DAY LATE';
        if ($date = $this->getPaidTo()) {
            $now = new \DateTime();
            $interval = $now->diff($date);
            $days = $interval->format('%d');
            if ($days > 1) {
                $result = $days.self::PAYMENT_LATE;
            }
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
            $result['status'] = strtoupper(ContractStatus::CURRENT);
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

    public function getActivePaymentHistory($em)
    {
        return $this->getPaymentHistory($em);
    }

    public function getFinishedPaymentHistory($em)
    {
        return $this->getPaymentHistory($em);
    }

    public function getFuturePaymentHistory($em)
    {
        return $this->getPaymentHistory($em);
    }

    public function getPaymentHistory($em)
    {
        $result = array('history' => array(), 'last_amount' => 0, 'last_date' => '');
        $payments = $this->createHistoryArray();
        $currentDate = new \DateTime('now');
        $lastDate = $currentDate->diff($this->getCreatedAt())->format('%r%a');
        $repo = $em->getRepository('DataBundle:Order');
        $orders = $repo->getContractHistory($this);
        foreach ($orders as $order) {
            $orderDate = $order->getCreatedAt();
            $interval = $currentDate->diff($orderDate)->format('%r%a');
            if ($interval > $lastDate) {
                $result['last_amount'] = $order->getAmount();
                $result['last_date'] = $order->getCreatedAt()->format('m/d/Y');
            }
            $nYear = $orderDate->format('Y');
            $nMonth = $orderDate->format('m');
            if ($late = $order->getDaysLate()) {
                $nMonth = $orderDate->modify('-'.$late.' days')->format('m');
                $payments[$nYear][$nMonth]['status'] = self::STATUS_LATE;
                $payments[$nYear][$nMonth]['text'] = $late;
                
            } else {
                $payments[$nYear][$nMonth]['status'] = self::STATUS_OK;
                $payments[$nYear][$nMonth]['text'] = self::PAYMENT_OK;
                
            }
            if (OrderStatus::NEWONE == $order->getStatus()) {
                $payments[$nYear][$nMonth]['status'] = self::STATUS_PAY;
                $payments[$nYear][$nMonth]['text'] = self::PAYMENT_AUTO;
            }
            $payments[$nYear][$nMonth]['amount']+= $order->getAmount();
        }
        $result['history'] = $payments;
        return $result;
    }

    private function createHistoryArray()
    {
        $result = array();
        $aMonthes = array();
        for ($i = 1; $i < 13; $i++) {
            $aMonthes[date('m', mktime(0, 0, 0, $i, 1))] = array(
                'status' => self::STATUS_EMPTY,
                'text' => '',
                'amount' => 0,
            );
        }
        $start = $this->getStartAt();
        $finish = $this->getFinishAt();
        $startYear = $start->format('Y');
        $finishYear = $finish->format('Y');
        for ($i = $startYear; $i <= $finishYear; $i++) {
            $result[$i] = $aMonthes;
        }
        if (ContractStatus::FINISHED != $this->getStatus()) {
            $paidTo = $this->getPaidTo();
            $result[$paidTo->format('Y')][$paidTo->format('m')] = array(
                'status' => self::STATUS_PAY,
                'text' => self::PAYMENT_PAY,
                'amount' => $this->getRent(),
            );
        }
        return $result;
    }

    public function shiftPaidTo($amount = null)
    {
        $paidTo = $this->getPaidTo();
        $newDate = clone $paidTo;
        $rent = $this->getRent();
        $amount = ($amount) ? $amount : $rent;
        if ($amount > $rent) {
            $newDate->modify('+1 months');
            $diff = $amount - $rent;
            $days = $this->countPaidDays($rent, $diff);
            $newDate->modify('+'.$days.' days');
        } elseif ($amount < $rent) {
            $days = $this->countPaidDays($rent, $amount);
            $newDate->modify('+'.$days.' days');
        } else {
            $newDate->modify('+1 months');
        }
        return $this->setPaidTo($newDate);
    }

    private function countPaidDays($rent, $paid)
    {
        return floor($paid * 30/ $rent);
    }

    public function getDatagridRow($em)
    {
        $property = $this->getProperty();
        $tenant = $this->getTenant();
        $unit = $this->getUnit();
        $repo = $em->getRepository('DataBundle:Order');
        $result = array();
        $result['id'] = $this->getId();
        $result['status'] = $this->getStatus();
        $result['full_address'] = $this->getRentAddress($property, $unit).' '.$property->getLocationAddress();
        $result['row_address'] = substr($result['full_address'], 0, 20).'...';
        $result['rent'] = ($rent = $this->getRent()) ? '$'.$rent : '--';
        $result['full_pay_to'] = $this->getHolding()->getName().' '.$this->getGroup()->getName();
        $result['row_pay_to'] = substr($result['full_pay_to'], 0, 10).'...';
        $result['status'] = $this->getStatus();
        $result['recur'] = 'NO';
        // @todo get payment source name
        $result['full_payment_source'] = 'N/A';
        $result['row_payment_source'] = substr($result['full_payment_source'], 0, 10).'...';
        $result['payment_last'] = 'N/A';
        if ($order = $repo->getLastContractPayment($this)) {
            $result['payment_last'] = $order->getUpdatedAt()->format('m/d/Y');
        }
        $result['payment_next'] = 'N/A';
        if ($paidTo = $this->getPaidTo()) {
            $result['payment_next'] = $paidTo->format('m/d/Y');
        }
        return $result;
    }
}
