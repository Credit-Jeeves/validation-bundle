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

//     /**
//      * @ORM\Column(
//      *     type="text",
//      *     nullable=true
//      * )
//      */
//     protected $message;

//     /**
//      * @ORM\Column(
//      *     type="boolean"
//      * )
//      */
//     protected $is_successfull = true;

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

//     /**
//      * @param text $message
//      * @return Heartland
//      */
//     public function setMessage($message = null)
//     {
//         $this->message = $message;
//         return $this;
//     }

//     /**
//      * @return text
//      */
//     public function getMessage()
//     {
//         return $this->message;
//     }

//     /**
//      * @param smallInt $isSuccesfull
//      * @return Heartland
//      */
//     public function setIsSuccessfull($isSuccessfull)
//     {
//         $this->is_successfull = $isSuccessfull;
//         return $this;
//     }

//     /**
//      * @return smallint
//      */
//     public function getIsSuccessfull()
//     {
//         return $this->is_successfull;
//     }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Heartland
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
