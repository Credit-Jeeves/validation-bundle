<?php
namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\Mapping as ORM;

/**
 * @TODO remove depositDate we already have it on order
 * @TODO make unique index by order id and apiType
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
abstract class OrderExternalApi
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Order",
     *     inversedBy="sentOrder"
     * )
     * @ORM\JoinColumn(
     *      name="order_id",
     *      referencedColumnName="id",
     *      nullable=false
     * )
     */
    protected $order;

    /**
     * @ORM\Column(
     *     type="ExternalApi",
     *     options={
     *         "default"="yardi"
     *     },
     *     name="api_type",
     *     nullable=false
     * )
     */
    protected $apiType;

    /**
     * @ORM\Column(
     *     name="deposit_date",
     *     type="date",
     *     nullable=false
     * )
     */
    protected $depositDate;

    /**
     * @param string $apiType
     */
    public function setApiType($apiType)
    {
        $this->apiType = $apiType;
    }

    /**
     * @return string
     */
    public function getApiType()
    {
        return $this->apiType;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setDepositDate(\DateTime $depositDate)
    {
        $this->depositDate = $depositDate;
    }

    /**
     * @return DateTime
     */
    public function getDepositDate()
    {
        return $this->depositDate;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Order $order
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }
}
