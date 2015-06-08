<?php

namespace RentJeeves\LandlordBundle\Model;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\LandlordBundle\Accounting\Import\Handler\HandlerAbstract;
use Symfony\Component\Form\Form;

class Import
{
    /**
     * @var array
     */
    protected $row = [];

    /**
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $isSkipped = false;

    /**
     * @Serializer\Type("string")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $uniqueKeyException;

    /**
     * @Serializer\Type("integer")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $importSummaryPublicId;

    /**
     * @Serializer\Type("string")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $skippedMessage;

    /**
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $hasContractWaiting = false;

    /**
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $isValidDateFormat = true;

    /**
     * @Serializer\Type("integer")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $number;

    /**
     * @Serializer\Type("RentJeeves\DataBundle\Entity\Tenant")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $tenant;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @Serializer\Type("RentJeeves\DataBundle\Entity\Contract")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $contract;

    /**
     * @Serializer\Type("RentJeeves\DataBundle\Entity\ResidentMapping")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $residentMapping = null;

    /**
     * @Serializer\Type("RentJeeves\DataBundle\Entity\PropertyMapping")
     */
    protected $propertyMapping;

    /**
     * @Serializer\Type("RentJeeves\DataBundle\Entity\UnitMapping")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $unitMapping = null;

    /**
     * @Serializer\Type("string")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $csrfToken = '';

    /**
     * @Serializer\Type("string")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $address = '';

    /**
     * @Serializer\Groups({"RentJeevesImport"})
     * @Serializer\Type("DateTime")
     */
    protected $moveOut = null;

    /**
     * @Serializer\Exclude
     */
    protected $form = null;

    /**
     * @Serializer\Type("array")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $errors = array();

    /**
     * @Serializer\Type("string")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $email;

    /**
     * @var boolean
     */
    protected $isMultipleProperty;

    /**
     * @var ContractWaiting
     */
    protected $contractWaiting;

    /**
     * @var HandlerAbstract
     */
    protected $handler;

    /**
     * @return array
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * @param array $row
     */
    public function setRow($row)
    {
        $this->row = $row;
    }

    /**
     * @return integer
     */
    public function getImportSummaryPublicId()
    {
        return $this->importSummaryPublicId;
    }

    /**
     * @param integer $importSummaryPublicId
     */
    public function setImportSummaryPublicId($importSummaryPublicId)
    {
        $this->importSummaryPublicId = $importSummaryPublicId;
    }

    /**
     * @return PropertyMapping
     */
    public function getPropertyMapping()
    {
        return $this->propertyMapping;
    }

    /**
     * @param PropertyMapping $propertyMapping
     */
    public function setPropertyMapping(PropertyMapping $propertyMapping)
    {
        $this->propertyMapping = $propertyMapping;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @param \RentJeeves\LandlordBundle\Accounting\Import\Handler\HandlerAbstract $handler
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;
    }

    /**
     * @return \RentJeeves\LandlordBundle\Accounting\Import\Handler\HandlerAbstract
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return string
     */
    public function getUniqueKeyException()
    {
        return $this->uniqueKeyException;
    }

    /**
     * @param string $uniqueKeyException
     */
    public function setUniqueKeyException($uniqueKeyException)
    {
        $this->uniqueKeyException = $uniqueKeyException;
    }

    /**
     * @return mixed
     */
    public function getSkippedMessage()
    {
        return $this->skippedMessage;
    }

    /**
     * @param mixed $skippedMessage
     */
    public function setSkippedMessage($skippedMessage)
    {
        $this->skippedMessage = $skippedMessage;
    }

    /**
     * @return mixed
     */
    public function getIsValidDateFormat()
    {
        return $this->isValidDateFormat;
    }

    /**
     * @param mixed $isValidDateFormat
     */
    public function setIsValidDateFormat($isValidDateFormat)
    {
        $this->isValidDateFormat = $isValidDateFormat;
    }

    /**
     * @return ContractWaiting
     */
    public function getContractWaiting()
    {
        return $this->contractWaiting;
    }

    /**
     * @param ContractWaiting $contractWaiting
     */
    public function setContractWaiting(ContractWaiting $contractWaiting)
    {
        $this->contractWaiting = $contractWaiting;
    }

    /**
     * @param boolean $hasContractWaiting
     */
    public function setHasContractWaiting($hasContractWaiting)
    {
        $this->hasContractWaiting = $hasContractWaiting;
    }

    /**
     * @return boolean
     */
    public function getHasContractWaiting()
    {
        return $this->hasContractWaiting;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param ResidentMapping $residentMapping
     */
    public function setResidentMapping(ResidentMapping $residentMapping)
    {
        $this->residentMapping = $residentMapping;
    }

    /**
     * @return ResidentMapping
     */
    public function getResidentMapping()
    {
        return $this->residentMapping;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        if (!isset($this->errors[$this->getNumber()])) {
            $this->errors[$this->getNumber()] = [];
        }

        return $this->errors;
    }

    /**
     * @return Contract
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * @param mixed $contract
     */
    public function setContract($contract)
    {
        $this->contract = $contract;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param Form $form
     */
    public function setForm(Form $form)
    {
        $this->form = $form;
    }

    /**
     * @return mixed
     */
    public function getMoveOut()
    {
        return $this->moveOut;
    }

    /**
     * @param mixed $moveOut
     */
    public function setMoveOut($moveOut)
    {
        $this->moveOut = $moveOut;
    }

    /**
     * @return integer
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param integer $number
     */
    public function setNumber($number)
    {
        $this->number = (int) $number;
    }

    /**
     * @return string
     */
    public function getCsrfToken()
    {
        return $this->csrfToken;
    }

    /**
     * @param string $csrfToken
     */
    public function setCsrfToken($csrfToken)
    {
        $this->csrfToken = $csrfToken;
    }

    /**
     * @return boolean
     */
    public function isSkipped()
    {
        return $this->isSkipped;
    }

    /**
     * @param boolean $isSkipped
     */
    public function setIsSkipped($isSkipped)
    {
        $this->isSkipped = $isSkipped;
    }

    /**
     * @return Tenant
     */
    public function getTenant()
    {
        return $this->tenant;
    }

    /**
     * @param Tenant $tenant
     */
    public function setTenant($tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * @param UnitMapping $unitMapping
     */
    public function setUnitMapping(UnitMapping $unitMapping)
    {
        $this->unitMapping = $unitMapping;
    }

    /**
     * @return UnitMapping|null
     */
    public function getUnitMapping()
    {
        return $this->unitMapping;
    }
}
