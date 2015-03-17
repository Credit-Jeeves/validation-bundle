<?php
namespace CreditJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class CheckoutAuthorizeNetAim
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="cj_order_id", type="bigint")
     */
    protected $cjOrderId;

    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\Order", inversedBy="authorizes")
     * @ORM\JoinColumn(name="cj_order_id", referencedColumnName="id")
     */
    protected $order;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="bigint")
     */
    protected $response_code;

    /**
     * @var string
     *
     * @ORM\Column(name="subcode", type="string", length=255)
     */
    protected $response_subcode;

    /**
     * @var string
     *
     * @ORM\Column(name="reason_code", type="bigint")
     */
    protected $response_reason_code;

    /**
     * @var string
     *
     * @ORM\Column(name="reason_text", type="string", length=255)
     */
    protected $response_reason_text;

    /**
     * @var string
     *
     * @ORM\Column(name="authorization_code", type="string", length=6)
     */
    protected $authorization_code;

    /**
     * @var string
     *
     * @ORM\Column(name="avs", type="string", length=1)
     */
    protected $avs_response;

    /**
     * @var string
     *
     * @ORM\Column(name="transaction_id", type="string", length=255)
     */
    protected $transaction_id;

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_number", type="string", length=20)
     */
    protected $invoice_number;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="method", type="string", length=255)
     */
    protected $method;

    /**
     * @var string
     *
     * @ORM\Column(name="transaction_type", type="string", length=255)
     */
    protected $transaction_type;

    /**
     * @var string
     *
     * @ORM\Column(name="md5_hash", type="string", length=255, nullable=true)
     */
    protected $md5_hash;

    /**
     * @var string
     *
     * @ORM\Column(name="purchase_order_number", type="string", length=25)
     */
    protected $purchase_order_number;

    /**
     * @var string
     *
     * @ORM\Column(name="card_code", type="string", length=1)
     */
    protected $card_code_response;

    /**
     * @var string
     *
     * @ORM\Column(name="cardholder_authentication_value", type="string", length=1, nullable=true)
     */
    protected $cardholder_authentication_value;

    /**
     * @var string
     *
     * @ORM\Column(name="split_tender_id", type="string", length=255, nullable=true)
     */
    protected $split_tender_id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Set cjOrderId
     *
     * @param integer $cjOrderId
     * @return CheckoutAuthorizeNetAim
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return CheckoutAuthorizeNetAim
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
