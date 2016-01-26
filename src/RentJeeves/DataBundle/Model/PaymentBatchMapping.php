<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use RentJeeves\DataBundle\Enum\PaymentBatchStatus;
use \DateTime;

/**
 * @ORM\MappedSuperclass
 */
abstract class PaymentBatchMapping
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(
     *     name="payment_batch_id",
     *     type="string",
     *     length=255,
     *     nullable=false
     * )
     * @Assert\NotBlank()
     */
    protected $paymentBatchId;

    /**
     * @ORM\Column(
     *     name="accounting_batch_id",
     *     type="string",
     *     length=255,
     *     nullable=false
     * )
     * @Assert\NotBlank()
     */
    protected $accountingBatchId;

    /**
     * @ORM\Column(
     *      type="PaymentBatchStatus",
     *      options={
     *         "default"="opened"
     *      },
     *      nullable=false
     * )
     */
    protected $status = PaymentBatchStatus::OPENED;

    /**
     * @ORM\Column(
     *      name="accounting_package_type",
     *      type="AccountingSystem",
     *      nullable=false
     * )
     */
    protected $accountingPackageType;

    /**
     * @ORM\Column(
     *      name="external_property_id",
     *      type="string",
     *      length=255,
     *      nullable=false
     * )
     */
    protected $externalPropertyId;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="opened_at",
     *     type="datetime"
     * )
     * @var DateTime
     */
    protected $openedAt;

    /**
     * @Gedmo\Timestampable(on="change", field="status", value="closed")
     * @ORM\Column(
     *     name="closed_at",
     *     type="datetime",
     *     nullable=true
     * )
     * @var DateTime
     */
    protected $closedAt;

    /**
     * @var int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $paymentBatchId
     * @return $this
     */
    public function setPaymentBatchId($paymentBatchId)
    {
        $this->paymentBatchId = $paymentBatchId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPaymentBatchId()
    {
        return $this->paymentBatchId;
    }

    /**
     * @param $accountingBatchId
     * @return $this
     */
    public function setAccountingBatchId($accountingBatchId)
    {
        $this->accountingBatchId = $accountingBatchId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccountingBatchId()
    {
        return $this->accountingBatchId;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $accountingPackageType
     * @return $this
     */
    public function setAccountingPackageType($accountingPackageType)
    {
        $this->accountingPackageType = $accountingPackageType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccountingPackageType()
    {
        return $this->accountingPackageType;
    }

    /**
     * @param $externalPropertyId
     * @return $this
     */
    public function setExternalPropertyId($externalPropertyId)
    {
        $this->externalPropertyId = $externalPropertyId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExternalPropertyId()
    {
        return $this->externalPropertyId;
    }

    /**
     * @param DateTime $openedAt
     * @return $this
     */
    public function setOpenedAt(DateTime $openedAt)
    {
        $this->openedAt = $openedAt;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getOpenedAt()
    {
        return $this->openedAt;
    }

    /**
     * @param DateTime $closedAt
     * @return $this
     */
    public function setClosedAt(DateTime $closedAt)
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getClosedAt()
    {
        return $this->closedAt;
    }
}
