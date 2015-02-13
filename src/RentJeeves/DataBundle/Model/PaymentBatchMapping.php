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
     *      name="payment_processor",
     *      type="PaymentProcessor",
     *      nullable=false
     * )
     */
    protected $paymentProcessor;

    /**
     * @ORM\Column(
     *      name="accounting_package_type",
     *      type="ApiIntegrationType",
     *      nullable=false
     * )
     */
    protected $accountingPackageType;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="created_at",
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
}
