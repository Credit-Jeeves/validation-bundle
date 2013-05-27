<?php
namespace CreditJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Payum\AuthorizeNet\Aim\Model\PaymentDetails;

/**
 * @ORM\MappedSuperclass
 */
class CheckoutAuthorizeNetAim extends PaymentDetails
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
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\Order")
     * @ORM\JoinColumn(name="cj_order_id", referencedColumnName="id")
     */
    protected $order;

    /**
     * @var integer
     *
     * @ORM\Column(name="cj_order_id", type="integer")
     */
    protected $cjOrderId;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer")
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="subcode", type="string", length=255)
     */
    protected $subcode;

    /**
     * @var string
     *
     * @ORM\Column(name="reason_code", type="integer")
     */
    protected $reasonCode;

    /**
     * @var string
     *
     * @ORM\Column(name="reason_text", type="string", length=255)
     */
    protected $reasonText;

    /**
     * @var string
     *
     * @ORM\Column(name="authorization_code", type="string", length=6)
     */
    protected $authorizationCode;

    /**
     * @var string
     *
     * @ORM\Column(name="avs", type="string", length=1)
     */
    protected $avs;

    /**
     * @var string
     *
     * @ORM\Column(name="transaction_id", type="string", length=255)
     */
    protected $transactionId;

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_number", type="string", length=20)
     */
    protected $invoiceNumber;

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
    protected $transactionType;

    /**
     * @var string
     *
     * @ORM\Column(name="md5_hash", type="string", length=255)
     */
    protected $md5Hash;

    /**
     * @var string
     *
     * @ORM\Column(name="purchase_order_number", type="string", length=25)
     */
    protected $purchaseOrderNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="card_code", type="string", length=25)
     */
    protected $cardCode;

    /**
     * @var string
     *
     * @ORM\Column(name="cardholder_authentication_value", type="string", length=1)
     */
    protected $cardholderAuthenticationValue;

    /**
     * @var string
     *
     * @ORM\Column(name="split_tender_id", type="string", length=255)
     */
    protected $splitTenderId;

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
     * Set code
     *
     * @param integer $code
     * @return CheckoutAuthorizeNetAim
     */
    public function setCode($code)
    {
        $this->code = $code;
    
        return $this;
    }

    /**
     * Get code
     *
     * @return integer 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set subcode
     *
     * @param string $subcode
     * @return CheckoutAuthorizeNetAim
     */
    public function setSubcode($subcode)
    {
        $this->subcode = $subcode;
    
        return $this;
    }

    /**
     * Get subcode
     *
     * @return string 
     */
    public function getSubcode()
    {
        return $this->subcode;
    }

    /**
     * Set reasonCode
     *
     * @param integer $reasonCode
     * @return CheckoutAuthorizeNetAim
     */
    public function setReasonCode($reasonCode)
    {
        $this->reasonCode = $reasonCode;
    
        return $this;
    }

    /**
     * Get reasonCode
     *
     * @return integer
     */
    public function getReasonCode()
    {
        return $this->reasonCode;
    }

    /**
     * Set reasonText
     *
     * @param string $reasonText
     * @return CheckoutAuthorizeNetAim
     */
    public function setReasonText($reasonText)
    {
        $this->reasonText = $reasonText;
    
        return $this;
    }

    /**
     * Get reasonText
     *
     * @return string 
     */
    public function getReasonText()
    {
        return $this->reasonText;
    }

    /**
     * Set authorizationCode
     *
     * @param string $authorizationCode
     * @return CheckoutAuthorizeNetAim
     */
    public function setAuthorizationCode($authorizationCode)
    {
        $this->authorizationCode = $authorizationCode;
    
        return $this;
    }

    /**
     * Get authorizationCode
     *
     * @return string 
     */
    public function getAuthorizationCode()
    {
        return $this->authorizationCode;
    }

    /**
     * Set avs
     *
     * @param string $avs
     * @return CheckoutAuthorizeNetAim
     */
    public function setAvs($avs)
    {
        $this->avs = $avs;
    
        return $this;
    }

    /**
     * Get avs
     *
     * @return string 
     */
    public function getAvs()
    {
        return $this->avs;
    }

    /**
     * Set transactionId
     *
     * @param string $transactionId
     * @return CheckoutAuthorizeNetAim
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
    
        return $this;
    }

    /**
     * Get transactionId
     *
     * @return string 
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * Set invoiceNumber
     *
     * @param string $invoiceNumber
     * @return CheckoutAuthorizeNetAim
     */
    public function setInvoiceNumber($invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;
    
        return $this;
    }

    /**
     * Get invoiceNumber
     *
     * @return string 
     */
    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return CheckoutAuthorizeNetAim
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set method
     *
     * @param string $method
     * @return CheckoutAuthorizeNetAim
     */
    public function setMethod($method)
    {
        $this->method = $method;
    
        return $this;
    }

    /**
     * Get method
     *
     * @return string 
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set transactionType
     *
     * @param string $transactionType
     * @return CheckoutAuthorizeNetAim
     */
    public function setTransactionType($transactionType)
    {
        $this->transactionType = $transactionType;
    
        return $this;
    }

    /**
     * Get transactionType
     *
     * @return string 
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * Set md5Hash
     *
     * @param string $md5Hash
     * @return CheckoutAuthorizeNetAim
     */
    public function setMd5Hash($md5Hash)
    {
        $this->md5Hash = $md5Hash;
    
        return $this;
    }

    /**
     * Get md5Hash
     *
     * @return string 
     */
    public function getMd5Hash()
    {
        return $this->md5Hash;
    }

    /**
     * Set purchaseOrderNumber
     *
     * @param string $purchaseOrderNumber
     * @return CheckoutAuthorizeNetAim
     */
    public function setPurchaseOrderNumber($purchaseOrderNumber)
    {
        $this->purchaseOrderNumber = $purchaseOrderNumber;
    
        return $this;
    }

    /**
     * Get purchaseOrderNumber
     *
     * @return string 
     */
    public function getPurchaseOrderNumber()
    {
        return $this->purchaseOrderNumber;
    }

    /**
     * Set car
     *
     * @param string $cardCode
     * @return CheckoutAuthorizeNetAim
     */
    public function setCardCode($cardCode)
    {
        $this->cardCode = $cardCode;
    
        return $this;
    }

    /**
     * Get car
     *
     * @return string 
     */
    public function getCardCode()
    {
        return $this->cardCode;
    }

    /**
     * Set cardholderAuthenticationValue
     *
     * @param string $cardholderAuthenticationValue
     * @return CheckoutAuthorizeNetAim
     */
    public function setCardholderAuthenticationValue($cardholderAuthenticationValue)
    {
        $this->cardholderAuthenticationValue = $cardholderAuthenticationValue;
    
        return $this;
    }

    /**
     * Get cardholderAuthenticationValue
     *
     * @return string 
     */
    public function getCardholderAuthenticationValue()
    {
        return $this->cardholderAuthenticationValue;
    }

    /**
     * Set splitTenderId
     *
     * @param string $splitTenderId
     * @return CheckoutAuthorizeNetAim
     */
    public function setSplitTenderId($splitTenderId)
    {
        $this->splitTenderId = $splitTenderId;
    
        return $this;
    }

    /**
     * Get splitTenderId
     *
     * @return string 
     */
    public function getSplitTenderId()
    {
        return $this->splitTenderId;
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
