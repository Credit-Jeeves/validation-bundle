<?php

namespace RentJeeves\PublicBundle\AccountingSystemIntegration;

use Symfony\Component\Validator\Constraints as Assert;

class ASIIntegratedModel implements \ArrayAccess
{
    /**
     * @var string
     * @see RentJeeves\DataBundle\Enum\AccountingSystem
     */
    protected $accountingSystem;

    /**
     * @var string
     * @Assert\NotBlank(message="error.accounting_system.integration.data.residentId.empty")
     */
    protected $residentId;

    /**
     * @var string
     * @Assert\NotBlank(message="error.accounting_system.integration.data.leaseId.empty")
     */
    protected $leaseId;

    /**
     * @var string
     * @Assert\NotBlank(groups={"mri"}, message="error.accounting_system.integration.data.holdingId.empty")
     */
    protected $holdingId;

    /**
     * @var string
     * @Assert\NotBlank(message="error.accounting_system.integration.data.propertyId.empty")
     */
    protected $propertyId;

    /**
     * @var string
     */
    protected $buildingId;

    /**
     * @var string
     */
    protected $unitId;

    /**
     * @var string
     */
    protected $returnUrl;

    /**
     * @var float
     */
    protected $rent;

    /**
     * @var float
     */
    protected $appFee;

    /**
     * @var float
     */
    protected $secDep;

    /**
     * @var array
     */
    protected $amounts = [];

    /**
     * @var array
     */
    protected $additionalParameters = [];

    /**
     * @param $accountingSystem
     */
    public function __construct($accountingSystem)
    {
        $this->accountingSystem = $accountingSystem;
    }

    /**
     * @return string
     */
    public function getAccountingSystem()
    {
        return $this->accountingSystem;
    }

    /**
     * @return string
     */
    public function getHoldingId()
    {
        return $this->holdingId;
    }

    /**
     * @param string $holdingId
     */
    public function setHoldingId($holdingId)
    {
        $this->holdingId = $holdingId;
    }

    /**
     * @return string
     */
    public function getResidentId()
    {
        return $this->residentId;
    }

    /**
     * @param string $residentId
     */
    public function setResidentId($residentId)
    {
        $this->residentId = $residentId;
    }

    /**
     * @return string
     */
    public function getLeaseId()
    {
        return $this->leaseId;
    }

    /**
     * @param string $leaseId
     */
    public function setLeaseId($leaseId)
    {
        $this->leaseId = $leaseId;
    }

    /**
     * @return string
     */
    public function getPropertyId()
    {
        return $this->propertyId;
    }

    /**
     * @param string $propertyId
     */
    public function setPropertyId($propertyId)
    {
        $this->propertyId = $propertyId;
    }

    /**
     * @return string
     */
    public function getBuildingId()
    {
        return $this->buildingId;
    }

    /**
     * @param string $buildingId
     */
    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;
    }

    /**
     * @return string
     */
    public function getUnitId()
    {
        return $this->unitId;
    }

    /**
     * @param string $unitId
     */
    public function setUnitId($unitId)
    {
        $this->unitId = $unitId;
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @param string $returnUrl
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    /**
     * @return float
     */
    public function getRent()
    {
        return $this->rent;
    }

    /**
     * @param float $rent
     */
    public function setRent($rent)
    {
        $this->rent = $rent;
    }

    /**
     * @return float
     */
    public function getAppFee()
    {
        return $this->appFee;
    }

    /**
     * @param float $appFee
     */
    public function setAppFee($appFee)
    {
        $this->appFee = $appFee;
    }

    /**
     * @return float
     */
    public function getSecDep()
    {
        return $this->secDep;
    }

    /**
     * @param float $secDep
     */
    public function setSecDep($secDep)
    {
        $this->secDep = $secDep;
    }

    /**
     * @return array
     */
    public function getAmounts()
    {
        return $this->amounts;
    }

    /**
     * @param array $amounts
     */
    public function setAmounts(array $amounts)
    {
        $this->amounts = $amounts;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return property_exists($this, $offset) ?: isset($this->additionalParameters[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return property_exists($this, $offset) ?
            $this->{$offset} :
            (isset($this->additionalParameters[$offset]) ? $this->additionalParameters[$offset] : null);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (property_exists($this, $offset)) {
            $this->{$offset} = $value;
        } else {
            $this->additionalParameters[$offset] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        if (property_exists($this, $offset)) {
            $this->{$offset} = null;
        }
        unset($this->additionalParameters[$offset]);
    }

    /**
     * @param $method
     * @param array $arguments
     * @return mixed|void
     */
    public function __call($method, array $arguments)
    {
        $property =  lcfirst(substr($method, 3));
        if (substr($method, 0, 3) == 'get') {
            if (isset($this->additionalParameters[$property])) {
                return $this->additionalParameters[$property];
            }
        }

        if (substr($method, 0, 3) == 'set') {
            $this->additionalParameters[$property] = array_shift($arguments);
        }
    }
}
