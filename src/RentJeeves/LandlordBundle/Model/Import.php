<?php

namespace RentJeeves\LandlordBundle\Model;

use CreditJeeves\DataBundle\Entity\Operation;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\Form\Form;

class Import
{
    /**
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $isSkipped;

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
     * @Serializer\Type("RentJeeves\DataBundle\Entity\Contract")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $contract;

    /**
     * @Serializer\Type("CreditJeeves\DataBundle\Entity\Operation")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $operation = null;

    /**
     * @Serializer\Type("string")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $csrfToken = '';

    /**
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $isValidUnit = false;

    /**
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $isValidResidentId = false;

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
     * @return boolean
     */
    public function getIsValidResidentId()
    {
        return $this->isValidResidentId;
    }

    /**
     * @param boolean $isValidResidentId
     */
    public function setIsValidResidentId($isValidResidentId)
    {
        $this->isValidResidentId = $isValidResidentId;
    }

    /**
     * @return boolean
     */
    public function getIsValidUnit()
    {
        return $this->isValidUnit;
    }

    /**
     * @param boolean $isValidUnit
     */
    public function setIsValidUnit($isValidUnit)
    {
        $this->isValidUnit = $isValidUnit;
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
        $this->number = (int)$number;
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
    public function getIsSkipped()
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
     * @return Operation
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * @param Operation $operation
     */
    public function setOperation(Operation $operation)
    {
        $this->operation = $operation;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"RentJeevesImport"})
     * @Serializer\Type("boolean")
     */
    public function isValid()
    {
        if ($this->isValidResidentId && $this->isValidUnit) {
            return true;
        }

        return false;
    }
}
