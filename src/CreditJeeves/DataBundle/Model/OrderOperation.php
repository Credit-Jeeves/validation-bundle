<?php
namespace CreditJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
class OrderOperation
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="cj_order_id", type="integer")
     */
    private $cjOrderId;

    /**
     * @var integer
     *
     * @ORM\Column(name="cj_operation_id", type="integer")
     */
    private $cjOperationId;

    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\Order", inversedBy="orderOperations")
     * @ORM\JoinColumn(name="cj_order_id", referencedColumnName="id")
     */
    protected $order;

    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\Operation", inversedBy="orderOperations")
     * @ORM\JoinColumn(name="cj_operation_id", referencedColumnName="id")
     */
    protected $operation;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set cjOrderId
     *
     * @param integer $cjOrderId
     * @return OrderOperation
     */
    public function setCjOrderId($cjOrderId)
    {
        $this->cjOrderId = $cjOrderId;
    
        return $this;
    }

    /**
     * Get cjOrderId
     *
     * @return integer 
     */
    public function getCjOrderId()
    {
        return $this->cjOrderId;
    }

    /**
     * @param \CreditJeeves\DataBundle\Entity\Order $order
     * @return CheckoutAuthorizeNetAim
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set cjOperationId
     *
     * @param integer $cjOperationId
     * @return OrderOperation
     */
    public function setCjOperationId($cjOperationId)
    {
        $this->cjOperationId = $cjOperationId;
    
        return $this;
    }

    /**
     * Get cjOperationId
     *
     * @return integer 
     */
    public function getCjOperationId()
    {
        return $this->cjOperationId;
    }
}
