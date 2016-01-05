<?php

namespace RentJeeves\CoreBundle\Serialization\Model;

use RentJeeves\DataBundle\Entity\Contract as Entity;
use CreditJeeves\DataBundle\Entity\Order;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Enum\ContractStatus;

class Contract
{
    /**
     * @var Order $lastPayment
     */
    protected $lastPayment;

    /**
     * @var Entity
     */
    protected $contract;

    /**
     * @var string
     */
    protected $revokeMessage;

    /**
     * @var array
     */
    protected $paidFor;

    /**
     * @param Entity $contract
     */
    public function __construct(Entity $contract)
    {
        $this->contract = $contract;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractPaidFor"})
     * @Serializer\SerializedName("paidForArr")
     *
     * @return array
     */
    public function getPaidForArray()
    {
        return $this->paidFor;
    }

    /**
     * @param array $paidFor
     */
    public function setPaidForArray(array $paidFor)
    {
        $this->paidFor = $paidFor;
    }

    /**
     * @param Order $lastPayment
     */
    public function setLastPayment(Order $lastPayment)
    {
        $this->lastPayment = $lastPayment;
    }

    /**
     * @return Order
     */
    public function getLastPayment()
    {
        return $this->lastPayment;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractList"})
     * @Serializer\SerializedName("revoke_message")
     *
     * @return string
     */
    public function getRevokeMessageString()
    {
        if ($this->revokeMessage) {
            return $this->revokeMessage;
        }

        return '';
    }

    /**
     * @param string $revokeMessage
     */
    public function setRevokeMessage($revokeMessage)
    {
        $this->revokeMessage = $revokeMessage;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("payment_setup")
     *
     * @return boolean
     */
    public function isPaymentSetup()
    {
        $lastPayment = $this->getLastPayment();

        return !empty($lastPayment);
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("last_payment")
     *
     * @return string
     */
    public function getLastPaymentDate()
    {
        if ($order = $this->getLastPayment()) {
            $order->getCreatedAt()->format('M d, Y');
        }

        return Entity::EMPTY_LAST_PAYMENT;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("id")
     *
     * @return int
     */
    public function getId()
    {
        return $this->contract->getId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("dueDate")
     *
     * @return string|int
     */
    public function getDueDate()
    {
        return $this->contract->getDueDate();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("balance")
     *
     * @return float
     */
    public function getBalance()
    {
        return $this->contract->getIntegratedBalance();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("status_name")
     *
     * @return string
     */
    public function getStatusName()
    {
        $status = $this->contract->getStatusArray();

        return $status['status_name'];
    }

    /**
     * @return array
     */
    public function getStatusArray()
    {
        $finish = $this->contract->getFinishAt();
        $statusArray = $this->contract->getStatusArray();
        $statusArray['style'] = $statusArray['class'];
        if (empty($finish)) {
            return $statusArray;
        }

        $today = new \DateTime();

        if ($today > $finish &&
            !in_array(
                $this->contract->getStatus(),
                [
                    ContractStatus::FINISHED,
                    ContractStatus::DELETED,
                ]
            )
        ) {
            $statusArray['style'] = 'contract-pending';
            $statusArray['status'] = Entity::CONTRACT_ENDED;
            $statusArray['status_label'] = [
                'label' => 'contract.statuses.contract_ended',
                'choice' => false,
                'params' => [
                    'status' => strtoupper($this->getStatusName())
                ],
            ];
        }

        return $statusArray;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("status")
     *
     * @return string
     */
    public function getStatus()
    {
        $status = $this->getStatusArray();

        return $status['status'];
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("status_label")
     *
     * @return string
     */
    public function getStatusLabel()
    {
        $status = $this->getStatusArray();

        return $status['status_label'];
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("style")
     *
     * @return string
     */
    public function getStyle()
    {
        $status = $this->getStatusArray();

        return $status['style'];
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("address")
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->contract->getRentAddress();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("full_address")
     *
     * @return string
     */
    public function getFullAddress()
    {
        $propertyAddress = $this->contract->getProperty()->getPropertyAddress();

        return sprintf('%s %s', $this->getAddress(), $propertyAddress->getLocationAddress());
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("property_id")
     *
     * @return int
     */
    public function getPropertyId()
    {
        return $this->contract->getProperty()->getId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("isDeniedOnExternalApi")
     *
     * @return boolean
     */
    public function isDeniedOnExternalApi()
    {
        return $this->contract->isDeniedOnExternalApi();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("unit_id")
     *
     * @return mixed
     */
    public function getUnitId()
    {
        if ($unit = $this->contract->getUnit()) {
            return $unit->getId();
        }

        return '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("isSingleProperty")
     *
     * @return boolean
     */
    public function isSingleProperty()
    {
        $propertyAddress = $this->contract->getProperty()->getPropertyAddress();

        return $propertyAddress->isSingle();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("tenant")
     *
     * @return string
     */
    public function getTenantFullName()
    {
        return ucwords(strtolower($this->contract->getTenant()->getFullName()));
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("first_name")
     *
     * @return string
     */
    public function getTenantFirstName()
    {
        return $this->contract->getTenant()->getFirstName();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("last_name")
     *
     * @return string
     */
    public function getTenantLastName()
    {
        return $this->contract->getTenant()->getLastName();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("email")
     *
     * @return string
     */
    public function getTenantEmail()
    {
        return $this->contract->getTenant()->getEmail();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("phone")
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->contract->getTenant()->getFormattedPhone();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("amount")
     *
     * @return mixed
     */
    public function getRent()
    {
        if ($rent = $this->contract->getRent()) {
            return $rent;
        }

        return '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("late")
     *
     * @return string
     */
    public function getLate()
    {
        return $this->contract->getLateDays();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("paid_to")
     *
     * @return mixed
     */
    public function getPaidTo()
    {
        if ($date = $this->contract->getPaidTo()) {
            return $date->format('M d, Y');
        }

        return '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("late_date")
     *
     * @return mixed
     */
    public function getLateDate()
    {
        if ($date = $this->contract->getPaidTo()) {
            return $date->format('M d, Y');
        }

        return '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("start")
     *
     * @return mixed
     */
    public function getStart()
    {
        if ($date = $this->contract->getStartAt()) {
            return $date->format('m/d/Y');
        }

        return '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("finish")
     *
     * @return mixed
     */
    public function getFinish()
    {
        if ($date = $this->contract->getFinishAt()) {
            return $date->format('m/d/Y');
        }

        return '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("reminder_revoke")
     *
     * @return boolean
     */
    public function isReminderRevoke()
    {
        return $this->contract->getStatus() === ContractStatus::INVITE;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("search")
     *
     * @return string
     */
    public function getSearch()
    {
        return $this->contract->getSearch();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("credit_track_enabled")
     *
     * @return boolean
     */
    public function isCreditTrackEnabled()
    {
        $tenant = $this->contract->getTenant();

        if ($setting = $tenant->getSettings()) {
            return (boolean) $setting->getCreditTrackPaymentAccount();
        } else {
            return false;
        }
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("residentId")
     *
     * @return mixed
     */
    public function getResidentId()
    {
        $tenant = $this->contract->getTenant();
        $residentMapping = $tenant->getResidentForHolding($this->contract->getHolding());

        return $residentMapping ? $residentMapping->getResidentId() : null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Contract"})
     * @Serializer\SerializedName("isIntegrated")
     *
     * @return boolean
     */
    public function isIntegrated()
    {
        $group = $this->contract->getGroup();

        if ($group && $groupSettings = $group->getGroupSettings()) {
            return $groupSettings->getIsIntegrated();
        }

        return false;
    }
}
