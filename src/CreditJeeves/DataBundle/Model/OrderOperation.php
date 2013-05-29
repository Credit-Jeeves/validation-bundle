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
