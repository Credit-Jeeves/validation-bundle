<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use RentJeeves\CheckoutBundle\Constraint\DayRangeValidator;
use RentJeeves\DataBundle\Enum\DisputeCode;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
use RentJeeves\DataBundle\Model\Contract as Base;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\ContractStatus;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Mapping\Annotation as Gedmo;
use RentJeeves\CoreBundle\DateTime;
use RuntimeException;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;
use RentJeeves\DataBundle\Validators\ContractDuplicate;

/**
 * Contract
 *
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\ContractRepository")
 * @ORM\Table(name="rj_contract")
 *
 * @Assert\Callback(methods={"isEndLaterThanStart"}, groups={"User", "Default"})
 * @ContractDuplicate()
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
     * @const string
     */
    const CURRENT_ACTIVE = 'ACTIVE';

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
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("groupId")
     * @Serializer\Groups({"payRent"})
     *
     * @return int
     */
    public function getGroupId()
    {
        return $this->getGroup()->getId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("disableCreditCard")
     * @Serializer\Groups({"payRent"})
     *
     * @return boolean
     */
    public function isDisableCreditCard()
    {
        return $this->getGroup()->isDisableCreditCard();
    }

    /**
     * @inheritdoc
     */
    public function getDueDate()
    {
        $day = parent::getDueDate();
        if (empty($day) && ($this->getStartAt() instanceof DateTime)) {
            $day = $this->getStartAt()->format('j');
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
     * @Serializer\Groups({"payRent"})
     *
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
     * @Serializer\Groups({"payRent"})
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

    public function isDeniedOnExternalApi()
    {
        if (in_array(
            $this->getPaymentAccepted(),
            PaymentAccepted::getDeniedValues()
        )) {
            return true;
        }

        return false;
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
        $result['dueDate'] = $this->getDueDate();
        $result['balance'] = $this->getCurrentBalance();
        $result['status'] = $status['status'];
        $result['status_name'] = $status['status_name'];
        $result['status_label'] = $status['status_label'];
        $result['style'] = $status['class'];
        $result['address'] = $this->getRentAddress($property, $unit);
        $result['full_address'] = $this->getRentAddress($property, $unit).' '.$property->getLocationAddress();
        $result['property_id'] = $property->getId();
        $result['isDeniedOnExternalApi'] = $this->isDeniedOnExternalApi();
        $result['unit_id'] = null;
        if ($unit) {
            $result['unit_id'] = $unit->getId();
        }
        $result['isSingleProperty'] = $property->getIsSingle();
        $result['tenant'] = ucwords(strtolower($tenant->getFullName()));
        $result['first_name'] = $tenant->getFirstName();
        $result['last_name'] = $tenant->getLastName();
        $result['email'] = $tenant->getEmail();
        $result['phone'] = $tenant->getFormattedPhone();
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
                $result['status_label'] = [
                    'label' => 'contract.statuses.contract_ended',
                    'choice' => false,
                    'params' => [
                        'status' => strtoupper($result['status_name'])
                    ],
                ];
            }
            $result['finish'] = $finish->format('m/d/Y');
        }
        $payments = $this->getPayments();
        $payment = $payments->first();

        $result['reminder_revoke'] = ($this->getStatus() === ContractStatus::INVITE) ? true : false;
        $result['payment_setup'] = ($payment) ? true : false;
        $result['search'] = $this->getSearch();

        if ($setting = $tenant->getSettings()) {
            $result['credit_track_enabled'] = !!$setting->getCreditTrackPaymentAccount();
        } else {
            $result['credit_track_enabled'] = false;
        }

        $residentMapping = $tenant->getResidentForHolding($this->getHolding());
        $result['residentId'] = $residentMapping ? $residentMapping->getResidentId() : null;

        $result['isIntegrated'] = false;
        if ($this->getGroup() && $groupSettings = $this->getGroup()->getGroupSettings()) {
            $result['isIntegrated'] = $groupSettings->getIsIntegrated();
        }

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
                    $payments[$order->getCreatedAt()->format('Y-m-d H:i:s')] = $order->getCreatedAt()->format('M d, Y');
                }
            }
            krsort($payments);

            return count($payments) ? current($payments) : $result;
        }
    }

    public function getStatusArray()
    {
        $result = array(
            'status' => strtoupper($this->getStatus()),
            'class' => '',
            'status_name' => $this->getStatus(),
            'status_label' => [
                'label' => 'contract.statuses.' . strtolower($this->getStatus()),
                'choice' => false,
                'params' => [],
            ]
        );
        if (ContractStatus::CURRENT == $this->getStatus()) {
            $result['status_name'] = self::CURRENT_ACTIVE;
        }
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
            $interval = $date->diff($now);
            $unpaidDays = $interval->format('%r%a');

            if ($unpaidDays <= 0 || $this->hasPaymentPendingForMonth($date->format('n'), $date->format('Y'))) {
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
                $result['status_label'] = [
                    'label' => 'contract.statuses.late',
                    'choice' => true,
                    'count' => $interval->days,
                    'params' => [
                        'status' => strtoupper($result['status_name']),
                        'count' => $interval->days
                    ]
                ];
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
        if (!$property->isSingle() && $unit) {
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
            foreach ($order->getRentOperations() as $operation) {
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
                            $payments[$nYear][$nMonth]['amount'] += $operation->getAmount();
                        }
                        break;
                    default:
                        continue 2;
                        break;
                }
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
        $currentYear = new DateTime('now');
        $finishYear = $currentYear->format('Y');
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

    /**
     * Fix mistake based on balance and due date
     *
     * @param DateTime $newDate
     *
     * @return DateTime
     */
    private function fixDay($newDate)
    {
        if (28 < $this->getDueDate() && $newDate->format('j') != $this->getDueDate() &&
            (0 == $this->getBalance() || abs($this->getBalance()) == $this->getRent())
        ) {
            if ($newDate->format('t') >= $this->getDueDate() && 3 >= abs($newDate->format('j') - $this->getDueDate())) {
                $newDate->setDate(null, null, $this->getDueDate());
            }
            if (3 >= $newDate->format('j')) {
                $newDate->modify('-1 month');
                $newDate->setDate(null, null, $this->getDueDate());
            }
        }

        return $newDate;
    }

    public function shiftPaidTo($amount = null)
    {
        // paid_to can not be calculated without rent
        if ($this->getRent() <= 0) {
            return;
        }

        $paidTo = $this->getPaidTo();
        $newDate = clone $paidTo;
        $rent = $this->getRent();
        $amount = ($amount) ? $amount : $rent;
        if ($amount > $rent) {
            $newDate->modify('+1 months');
            $diff = $amount - $rent;
            $days = $this->countPaidDays($rent, $diff, $paidTo);
            $newDate->modify('+'.$days.' days');
        } elseif ($amount < $rent) {
            $days = $this->countPaidDays($rent, $amount, $paidTo);
            $newDate->modify('+'.$days.' days');
        } else {
            $newDate->modify('+1 months');
        }

        return $this->setPaidTo($this->fixDay($newDate));
    }

    /**
     * To avoid any failures it does the same as shiftPaidTo, but sets paidTo back.
     */
    public function unshiftPaidTo($amount)
    {
        // paid_to can not be calculated without rent
        if ($this->getRent() <= 0) {
            return;
        }

        $paidTo = $this->getPaidTo();
        $newDate = clone $paidTo;
        $rent = $this->getRent();
        $amount = $amount ?: $rent;
        if ($amount > $rent) {
            $newDate->modify('-1 months');
            $diff = $amount - $rent;
            $days = $this->countPaidDays($rent, $diff, $newDate);
            $newDate->modify('-'.$days.' days');
        } elseif ($amount < $rent) {
            $days = $this->countPaidDays($rent, $amount, $newDate);
            $newDate->modify('-'.$days.' days');
        } else {
            $newDate->modify('-1 months');
        }

        return $this->setPaidTo($this->fixDay($newDate));
    }

    /**
     * @param float $rent
     * @param float $paid
     * @param DateTime $date
     *
     * @return float
     */
    private function countPaidDays($rent, $paid, $date)
    {
        $days = $date->format('t');

        return floor($paid * $days/ $rent);
    }

    /**
     * @TODO make it with serializer, it's sucks currently
     * @TODO remove EntityManager Entity must not use it
     *
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
            $result['payment_amount'] = $payment->getTotal();

            $result['row_payment_source'] = $payment->getPaymentAccount()->getName();
            if (10 < strlen($result['row_payment_source'])) {
                $result['row_payment_source'] = substr($result['row_payment_source'], 0, 10).'...';
                $result['full_payment_source'] = $payment->getPaymentAccount()->getName();
            }
        }

        $result['due_on'] = 'N/A';
        if ($this->getDueDate()) {
            $result['due_on'] = $this->getDueDate();
        }
        $result['start_at'] = $this->getStartAt();
        $result['finish_at'] = $this->getFinishAt();
        $result['amount'] = $this->getRent();
        $result['property'] = $this->getProperty()->getItem();
        $result['group_id'] = $this->getGroup()->getId();
        $groupSettings = $this->getGroup()->getGroupSettings();
        $isIntegrated = $groupSettings->getIsIntegrated();
        $result['is_integrated'] = $isIntegrated;
        $result['isDeniedOnExternalApi'] = $this->isDeniedOnExternalApi();
        $result['is_allowed_to_pay'] =
            ($groupSettings->getPayBalanceOnly() == true && $this->getIntegratedBalance() <= 0) ? false : true;
        // display only integrated balance
        $result['balance'] = $isIntegrated ? sprintf('$%s', $this->getIntegratedBalance()) : '';
        $result['in_day_range'] = DayRangeValidator::inRange(
            new DateTime(),
            $groupSettings->getOpenDate(),
            $groupSettings->getCloseDate()
        );
        $result['open_day'] = $groupSettings->getOpenDate();
        $result['close_day'] = $groupSettings->getCloseDate();

        return $result;
    }

    public function setStatusApproved()
    {
        $startAt = $this->getStartAt();
        $dueDateFromStartAt = (int) $startAt->format('j');
        $paidTo = new DateTime();
        $paidTo->setDate($startAt->format('Y'), $startAt->format('n'), null);
        if ($dueDateFromStartAt > $this->getDueDate()) {
            $paidTo->modify("+1 month");
        }
        $paidTo->setDate(null, null, $this->getDueDate());
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
     * @Serializer\Groups({"payRent"})
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

    /**
     * @return string
     */
    public function __toString()
    {
        $toString = '';
        if ($this->getProperty()) {
            $toString = $this->getProperty()->getAddress();
            if ($this->getUnit()) {
                try {
                    $unitName = ' #' . $this->getUnit()->getName();
                } catch (EntityNotFoundException $e) { // hack for lazyload and soft delete
                    $unitName = '';
                }
                $toString .= $unitName;
            }
        }

        return $toString;
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
        $startAt = new DateTime($this->getPaidTo()->format('c'));
        if ($this->getDueDate() < $startAt->format('j')) {
            $startAt->modify('+1 month');
        }

        return $startAt->setDate(null, null, $this->getDueDate());
    }

    public function isEndLaterThanStart(ExecutionContextInterface $validatorContext)
    {
        if (!$this->getStartAt() || !$this->getFinishAt()) {
            return;
        }
        if ($this->getFinishAt() < $this->getStartAt()) {
            $validatorContext->addViolationAt('finish', 'contract.error.is_end_later_than_start', array(), null);
        }
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("depositAccount")
     * @Serializer\Type("RentJeeves\DataBundle\Entity\DepositAccount")
     * @Serializer\Groups({"payRent"})
     */
    public function getDepositAccount()
    {
        return $this->getGroup()->getDepositAccount();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("groupSetting")
     * @Serializer\Type("RentJeeves\DataBundle\Entity\GroupSettings")
     * @Serializer\Groups({"payRent"})
     *
     * @return GroupSettings
     */
    public function getSettings()
    {
        return $this->getGroup()->getGroupSettings();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("paidTo")
     * @Serializer\Type("DateTime<'Y-m-d 00:00:00'>")
     * @Serializer\Groups({"payRent"})
     *
     * @return int
     */
    public function getFormattedPaidTo()
    {
        return $this->getPaidTo();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("startAt")
     * @Serializer\Type("DateTime<'Y-m-d 00:00:00'>")
     * @Serializer\Groups({"payRent"})
     *
     * @return int
     */
    public function getFormattedStartAt()
    {
        return $this->getStartAt();
    }

    public function getCurrentBalance()
    {
        if ($this->getSettings()->getIsIntegrated()) {
            return $this->getIntegratedBalance();
        } else {
            return $this->getBalance();
        }
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("name")
     * @Serializer\Groups({"lateEmailReport"})
     *
     * @return string| null
     */
    public function getTenantFullName()
    {
        return $this->getTenant() ? $this->getTenant()->getFullName() : null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("email")
     * @Serializer\Groups({"lateEmailReport"})
     *
     * @return string| null
     */
    public function getTenantEmail()
    {
        return $this->getTenant() ? $this->getTenant()->getEmail() : null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("address")
     * @Serializer\Groups({"lateEmailReport"})
     *
     * @return string| null
     */
    public function getTenantRentAddress()
    {
        return $this->getRentAddress($this->getProperty(), $this->getUnit());
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("late")
     * @Serializer\Groups({"lateEmailReport"})
     *
     * @return int
     */
    public function getTenantLateDays()
    {
        return $this->getPaidTo()->diff(new DateTime())->format('%d');
    }

    /**
     * @param $month
     * @param $year
     * @return bool
     */
    public function hasPaymentPendingForMonth($month, $year)
    {
        $requiredMonth = new DateTime();
        $requiredMonth->setDate($year, $month, 1);

        $operations = $this->getOperations();
        $count = $operations->count();
        for ($i = 1; $i <= $count; $i++) {

            /** @var $operation Operation */
            $operation = $operations->slice($count-$i, 1);
            $operation = reset($operation);
            if ($operation->getType() == OperationType::RENT) {
                $order = $operation->getOrder();
                $paidFor = $operation->getPaidFor();
                $paymentMonth = new DateTime();
                $paymentMonth->setDate($paymentMonth->format('Y'), $paymentMonth->format('n'), 1);

                if ($order && $paidFor) {
                    if (($order->getStatus() == OrderStatus::PENDING)
                        && ($paymentMonth == $requiredMonth)) {
                        return true;
                    }
                }

                return false;
            }
        }

        return false;
    }

    /**
     * @link https://credit.atlassian.net/browse/RT-1006
     *
     * Setting paidTo logic from contract waiting.
     * Use this method only when contract created from contract waiting and need set up paidTo.
     */
    public function setPaidToByBalanceDue()
    {
        $this->getDueDate();
        $paidTo = new DateTime();
        $paidTo->setDate(
            null,
            null,
            $this->getDueDate()
        );
        //if balance is >0 then balance due, set paidTo to duedate to current month.
        if ($this->getIntegratedBalance() > 0) {
            $this->setPaidTo($paidTo);

            return;
        }
        $paidTo->modify('+1 month');
        //if balance is <=0 then no balance due, set paidTo to duedate in month future. (+1)
        $this->setPaidTo($paidTo);
    }
}
