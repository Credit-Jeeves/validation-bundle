<?php

namespace RentJeeves\LandlordBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\Form\Form;

/**
 * @Serializer\XmlRoot("Import")
 */
class Import
{
    /**
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("isSkipped")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $isSkipped;

    /**
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("number")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $number;

    /**
     * @Serializer\Type("RentJeeves\DataBundle\Entity\Tenant")
     * @Serializer\SerializedName("Tenant")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $tenant;

    /**
     * @Serializer\Type("RentJeeves\DataBundle\Entity\Contract")
     * @Serializer\SerializedName("Contract")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $contract;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("csrfToken")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $csrfToken = '';

    /**
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("isValidUnit")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $isValidUnit = false;

    /**
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("isValidResidentId")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $isValidResidentId = false;

    /**
     * @Serializer\SerializedName("moveOut")
     * @Serializer\Groups({"CreditJeeves"})
     * @Serializer\Type("DateTime")
     */
    protected $moveOut = null;

    /**
     * @Serializer\Exclude
     */
    protected $form = false;

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
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("isValid")
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
