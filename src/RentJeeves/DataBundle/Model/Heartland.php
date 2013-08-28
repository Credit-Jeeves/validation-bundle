<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Payum\Heartland\Bridge\Doctrine\Entity\PaymentDetails;
use \DateTime;

/**
 * @ORM\MappedSuperclass
 */
abstract class Heartland extends PaymentDetails
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @var \CreditJeeves\DataBundle\Entity\Order
     *
     * @ORM\ManyToOne(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Order",
     *     inversedBy="heartlands"
     * )
     *
     * @ORM\JoinColumn(
     *     name="order_id",
     *     referencedColumnName="id"
     * )
     */
    protected $order;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="created_at",
     *     type="datetime"
     * )
     * @var DateTime
     */
    protected $createdAt;

    /**
     * Set Order
     *
     * @param \CreditJeeves\DataBundle\Entity\Order
     * @return contract
     */
    public function setOrder(\CreditJeeves\DataBundle\Entity\Order $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Get Order
     *
     * @return \CreditJeeves\DataBundle\Entity\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return PaymentDetails
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
