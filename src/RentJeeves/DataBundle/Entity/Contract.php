<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Enum\DisputeCode;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentType;
use RentJeeves\DataBundle\Model\Contract as Base;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\ContractStatus;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Mapping\Annotation as Gedmo;
use RentJeeves\CoreBundle\DateTime;
use RuntimeException;

/**
 * Contract
 *
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\ContractRepository")
 * @ORM\Table(name="rj_contract")
 *
 * @Gedmo\Loggable(logEntryClass="RentJeeves\DataBundle\Entity\ContractHistory")
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

    /**
     * @var string
     */
    const STATUS_EMPTY = 'empty-month';

    /**
     * @var string
     */
    const CONTRACT_ENDED = 'CONTRACT ENDED';

    /**
     * @var string
     */
    const STATUS_PAY = 'auto';

    /**
     * @var string
     */
    const STATUS_OK = 'C';

    /**
     * @var string
     */
    const STATUS_LATE = '1';

    /**
     * @var string
     */
    const EMPTY_LAST_PAYMENT = '-';

    /**
     * Details in RT-65
     * On the tenant tab we need use false
     * On the dashboard tab we need use true
     *
     * @var bool
     */
    protected $statusShowLateForce = false;

    /**
     * @param boolean $statusShowLateForce
     */
    public function setStatusShowLateForce($statusShowLateForce)
    {
        $this->statusShowLateForce = $statusShowLateForce;
    }

    /**
     * @return boolean
     */
    public function getStatusShowLateForce()
    {
        return $this->statusShowLateForce;
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        return $this->getGroup()->getId();
    }

    /**
     * @inheritdoc
     */
    public function getDueDate()
    {
        $day = parent::getDueDate();
        if (empty($day) && ($startAt = $this->getStartAt())) {
            $day = $startAt->format('j');
        }
        return $day;
    }

    /**
     * @inheritdoc
     */
    public function getPaidTo()
    {
        $date = parent::getPaidTo();
        if (empty($date)) {
            $date = $this->getStartAt();
        }
        return $date;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("payToName")
     * @return string
     */
    public function getPayToName()
    {
        $holdingName = $this->getHolding()->getName();
        $groupName = $this->getGroup()->getName();
        if ($holdingName != $groupName) {
            return $holdingName . ' ' . $groupName;
        }
        return $holdingName;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("payment")
     * @Serializer\Type("RentJeeves\DataBundle\Entity\Payment")
     *
     * @return Payment
     */
    public function getNotClosedPayment()
    {
        /** @var Payment $payment */
        if (($payments = $this->getPayments()) && ($payment = $payments->last())) {
            if (PaymentStatus::CLOSE != $payment->getStatus()) {
                return $payment;
            }
        }
        return null;
    }

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
        $result['status_name'] = $status['status_name'];
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
        $result['amount'] = '';
        if ($rent = $this->getRent()) {
            $result['amount'] = $this->getRent();
        }
        $result['late'] = $this->getLateDays();
        $result['paid_to'] = '';
        $result['late_date'] = '';
        $result['last_payment'] = $this->getLastPayment();
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
            $today = new DateTime();
            if ($today > $finish &&
                !in_array(
                    $this->getStatus(),
                    array(
                        ContractStatus::FINISHED,
                        ContractStatus::DELETED,
                    )
                )
            ) {
                $result['style'] = 'contract-pending';
                $result['status'] = self::CONTRACT_ENDED;
            }
            $result['finish'] = $finish->format('m/d/Y');
        }
        $payments = $this->getPayments();
        $payment = $payments->first();

        $result['reminder_revoke'] = ($this->getStatus() === ContractStatus::INVITE)? true : false;
        $result['payment_setup'] = ($payment)? true : false;
        $result['search'] = $this->getSearch();

        return $result;
    }

    private function getLateDays()
    {
        $result = '1 DAY LATE'; //TODO it is wrong because PaidTo==null can't be 1 day late
        if ($date = $this->getPaidTo()) {
            $now = new DateTime();
            $interval = $now->diff($date);
            if ($interval->format("%r%a") < 0) {
                $result = $interval->days.self::PAYMENT_LATE;
            }
        }
        return $result;
    }

    public function getLastPayment()
    {
        $result = self::EMPTY_LAST_PAYMENT;
        $payments = array();
        $operations = $this->getOperations();
        if (!$operations->count()) {
            return $result;
        } else {
            /** @var Operation $operation */
            foreach ($operations as $operation) {
                $order = $operation->getOrder();
                if (OrderStatus::COMPLETE == $order->getStatus()) {
                    $payments[] = $order->getCreatedAt()->format('M d, Y');
                }
            }
            arsort($payments);
            return isset($payments[0]) ? $payments[0] : $result;
        }
    }

    public function getStatusArray()
    {
        $result = array('status' => strtoupper($this->getStatus()), 'class' => '', 'status_name' => $this->getStatus());
        if (ContractStatus::PENDING == $this->getStatus()) {
            $result['class'] = 'contract-pending';
            return $result;
        }
        if (ContractStatus::FINISHED == $this->getStatus()) {
            $result['status'] = strtoupper(ContractStatus::FINISHED);
            $result['class'] = '';
            return $result;
        }
        if ($date = $this->getPaidTo()) {
            $now = new DateTime();
            $interval = $now->diff($date);
            $sign = $interval->format('%r');
            if (!$sign) {
                return $result;
            }
            $lastPayment = $this->getLastPayment();
            /**
             * if we have payments for this contract need show days late
             * don't show days late for finished and delete contract
             */
            if (($lastPayment != self::EMPTY_LAST_PAYMENT &&
                !in_array(
                    $this->getStatus(),
                    array(
                        ContractStatus::FINISHED,
                        ContractStatus::DELETED,
                    )
                ))
                ||
                ($this->getStatusShowLateForce() && $result['status'] == strtoupper(ContractStatus::CURRENT))
            ) {
                $result['status'] = 'LATE ('.$interval->days.' days)';
                $result['status_name'] = 'late';
                $result['class'] = 'contract-late';
                return $result;
            }

            /**
             * If tenant is approved, but has never made payment before, just show Approved
             * without red shading for status
             */
            if ($result['status'] == strtoupper(ContractStatus::APPROVED) ||
                $result['status'] == strtoupper(ContractStatus::INVITE)) {
                $result['class'] = 'contract-late';
                return $result;
            }

            return $result;
        }
        $result['status'] = strtoupper($this->getStatus());
        $result['class'] = '';
        return $result;
    }

    /**
     * @param $property
     * @param $unit
     * @return string
     */
    public function getRentAddress($property = null, $unit = null)
    {
        $result = array();
        if (!$property) {
            $property = $this->getProperty();
        }
        if (!$unit) {
            $unit = $this->getUnit();
        }
        $result[] = $property->getAddress();
        if ($unit) {
            $result[] = $unit->getName();
            $result = implode(' #', $result);
        } else {
            $result = implode('', $result);
        }
        return $result;
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

    /**
     * @param EntityManager $em
     *
     * @return array
     */
    public function getPaymentHistory($em)
    {
        $result = array('history' => array(), 'last_amount' => 0, 'last_date' => '-');
        $payments = $this->createHistoryArray();
        $currentDate = new DateTime('now');
        $lastDate = $currentDate->diff($this->getCreatedAt())->format('%r%a');
        $repo = $em->getRepository('DataBundle:Order');
        $orders = $repo->getContractHistory($this);
        /** @var Order $order */
        foreach ($orders as $order) {
            $operation = $order->getRentOperation();
            $paidFor = $operation->getPaidFor();
            $lastPaymentDate = $order->getCreatedAt()->format('m/d/Y');
            $late = $operation->getDaysLate();
            $nYear = $paidFor->format('Y');
            $nMonth = $paidFor->format('m');
            $interval = $currentDate->diff($paidFor)->format('%r%a');
            $status = $order->getStatus();
            switch ($status) {
                case OrderStatus::NEWONE:
                    $payments[$nYear][$nMonth]['status'] = self::STATUS_PAY;
                    $payments[$nYear][$nMonth]['text'] = self::PAYMENT_AUTO;
                    break;
                case OrderStatus::COMPLETE:
                    if ($interval >= $lastDate) {
                        $result['last_amount'] = $operation->getAmount();
                        $result['last_date'] = $lastPaymentDate;
                    }
                    $payments[$nYear][$nMonth]['status'] = self::STATUS_OK;
                    $payments[$nYear][$nMonth]['text'] = self::PAYMENT_OK;
                    if ($late >= 30) {
                        $payments[$nYear][$nMonth]['status'] = self::STATUS_LATE;
                        $lateText = floor($late / 30) * 30;
                        $payments[$nYear][$nMonth]['text'] = $lateText;
                    }
                    if (!isset($payments[$nYear][$nMonth]['amount'])) {
                        $payments[$nYear][$nMonth]['amount'] = $operation->getAmount();
                    } else {
                        $payments[$nYear][$nMonth]['amount']+= $operation->getAmount();
                    }
                    break;
                default:
                    continue 2;
                    break;
            }
        }
        ksort($payments);
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
        $finishYear = $startYear;
        if ($finish) {
            $finishYear = $finish->format('Y');
        }
        for ($i = $startYear; $i <= $finishYear; $i++) {
            $result[$i] = $aMonthes;
        }
        /*if (ContractStatus::FINISHED != $this->getStatus()) {
            $paidTo = $this->getPaidTo();
            $result[$paidTo->format('Y')][$paidTo->format('m')] = array(
                'status' => self::STATUS_PAY,
                'text' => self::PAYMENT_PAY,
                'amount' => 0,
            );
        }*/
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

    /**
     * To avoid any failures it does the same as shiftPaidTo, but sets paidTo back.
     */
    public function unshiftPaidTo($amount)
    {
        $paidTo = $this->getPaidTo();
        $newDate = clone $paidTo;
        $rent = $this->getRent();
        $amount = $amount?:$rent;
        if ($amount > $rent) {
            $newDate->modify('-1 months');
            $diff = $amount - $rent;
            $days = $this->countPaidDays($rent, $diff);
            $newDate->modify('-'.$days.' days');
        } elseif ($amount < $rent) {
            $days = $this->countPaidDays($rent, $amount);
            $newDate->modify('-'.$days.' days');
        } else {
            $newDate->modify('-1 months');
        }

        return $this->setPaidTo($newDate);
    }

    private function countPaidDays($rent, $paid)
    {
        return floor($paid * 30/ $rent);
    }

    /**
     * @param EntityManager $em
     *
     * @return array
     */
    public function getDatagridRow($em)
    {
        $property = $this->getProperty();
        $unit = $this->getUnit();
        $orderRepository = $em->getRepository('DataBundle:Order');
        $result = array();
        $result['id'] = $this->getId();
        $result['full_address'] = $this->getRentAddress($property, $unit).' '.$property->getLocationAddress();
        $result['row_address'] = substr($result['full_address'], 0, 20).'...';
        $result['rent'] = ($rent = $this->getRent()) ? '$'.$rent : '--';
        $result['full_pay_to'] = $this->getPayToName();
        $result['row_pay_to'] = substr($result['full_pay_to'], 0, 10).'...';
        $result['status'] = $this->getStatus();
        $result['payment_type'] = '';
        // @todo get payment source name
        $result['row_payment_source'] = 'N/A';
        $result['payment_last'] = 'N/A';
        $result['full_payment_source'] = '';
        $result['isPayment'] = false;
        $result['payment_status'] = false;
        /** @var Order $lastOrder */
        $lastOrder = $orderRepository->getLastContractPayment($this);
        $lastPaymentDate = null;
        if ($lastOrder) {
            $result['payment_last'] = $lastOrder->getUpdatedAt()->format('m/d/Y');
            $lastPaymentDate = $lastOrder->getUpdatedAt();
            $now = new DateTime();
            $today = $now->format('Ymd');
            if ($lastOrder->getStatus() == OrderStatus::PENDING
                && $today == $lastPaymentDate->format('Ymd')
            ) {
                $result['payment_status'] = 'processing';
            }
        }

        if ($payment = $this->getNotClosedPayment()) {
            $result['isPayment'] = true;
            $result['payment_type'] = $payment->getType();
            $result['payment_due_date'] = $payment->getNextPaymentDate($lastPaymentDate)->format('m/d/Y');

            $result['row_payment_source'] = $payment->getPaymentAccount()->getName();
            if (10 < strlen($result['row_payment_source'])) {
                $result['row_payment_source'] = substr($result['row_payment_source'], 0, 10).'...';
                $result['full_payment_source'] = $payment->getPaymentAccount()->getName();
            }
        }

        $result['payment_next'] = 'N/A';
        if ($paidTo = $this->getPaidTo()) {
            $result['payment_next'] = $paidTo->format('m/d/Y');
        }
        $result['start_at'] = $this->getStartAt();
        $result['finish_at'] = $this->getFinishAt();
        $result['amount'] = $this->getRent();
        $result['property'] = $this->getProperty()->getItem();
        $result['group_id'] = $this->getGroup()->getId();
        return $result;
    }

    public function setStatusApproved()
    {
        $startAt = $this->getStartAt();
        $paidTo = clone $startAt;
        $this->setPaidTo($paidTo);
        $this->setStatus(ContractStatus::APPROVED);
        return $this;
    }

    /**
     * Tenant could do mistake with payment periods or contract could de changed
     * For this case we'll check paid to period
     */
    public function checkPaidTo()
    {
        $result = false;
        $paidTo = $this->getPaidTo();
        $finish = $this->getFinishAt();
        $interval = $finish->diff($paidTo)->format('%r%a');
        if ($interval < 0) {
            $result = true;
        }
        return $result;
    }

    public function initiatePaidTo()
    {
        $paidTo = $this->getPaidTo();
        if (empty($paidTo)) {
            $startAt = $this->getStartAt();
            $paidTo = clone $startAt;
            $this->setPaidTo($paidTo);
        }
        return $this;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("isPidVerificationSkipped")
     * @Serializer\Type("boolean")
     *
     * @return boolean
     */
    public function getIsPidVerificationSkipped()
    {
        return $this->getGroup()->getGroupSettings()->getIsPidVerificationSkipped();
    }

    public function getAvailableDisputeCodes()
    {
        return DisputeCode::all();
    }

    public function __toString()
    {
        return $this->getProperty()->getAddress() . ($this->getUnit()?' #' . $this->getUnit()->getName():'');
    }

    /**
     * @throws RuntimeException
     *
     * @return Payment
     */
    public function getActivePayment()
    {
        $collection = $this->getPayments()->filter(
            function (Payment $payment) {
                if (PaymentStatus::ACTIVE == $payment->getStatus()) {
                    return true;
                }

                return false;
            }
        );
        if (1 < $collection->count()) {
            throw new RuntimeException(sprintf('Contract "%s" have more ten one active payments', $this->getId()));
        }
        if (0 == $collection->count()) {
            return null;
        }
        return $collection[0];
    }


    /**
     * Have test by path: src/RentJeeves/DataBundle/Tests/Unit/ContractEntityCase.php
     *
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"RentJeevesImport"})
     * @Serializer\Type("boolean")
     *
     * @return bool
     */
    public function isLate()
    {
        $paidTo = $this->getPaidTo();

        if (empty($paidTo) || !is_null($this->getId())) {
            return false;
        }

        $today = new DateTime();
        $interval = $today->diff($paidTo)->format("%r%a");

        if ($interval > 0) {
            return false;
        }

        return true;
    }

    /**
     * @return DateTime | null
     */
    public function getPaidToWithDueDate()
    {
        if (!$this->getPaidTo() || !$this->getDueDate()) {
            return null;
        }
        if ($this->getDueDate() == $this->getPaidTo()->format('j')) {
            return $this->getPaidTo();
        }
        $startAt = new DateTime();
        $startAt->setDateTime($this->getPaidTo());
        if ($this->getDueDate() < $startAt->format('j')) {
            $startAt->modify('+1 month');
        }

        return $startAt->setDate(null, null, $this->getDueDate());
    }
}
