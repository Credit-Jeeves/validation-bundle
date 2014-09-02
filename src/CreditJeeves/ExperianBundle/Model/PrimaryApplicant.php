<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("PrimaryApplicant")
 */
class PrimaryApplicant
{
    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Name")
     * @Serializer\Groups({"PreciseID"})
     * @var Name
     */
    protected $name;

    /**
     * @Serializer\SerializedName("SSN")
     * @Serializer\Groups({"PreciseID"})
     * @var int
     */
    protected $ssn;

    /**
     * @Serializer\Groups({"PreciseID"})
     * @var CurrentAddress
     */
    protected $currentAddress;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\PreviousAddress")
     * @Serializer\Groups({"PreciseID"})
     * @var PreviousAddress
     */
    protected $previousAddress = null;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Phone")
     * @Serializer\Groups({"PreciseID"})
     * @var Phone
     */
    protected $phone = null;

    /**
     * @Serializer\SerializedName("Employment")
     * @Serializer\Groups({"PreciseID"})
     * @var
     */
    protected $employment = null;

    /**
     * @Serializer\SerializedName("Age")
     * @Serializer\Groups({"PreciseID"})
     * @var int
     */
    protected $age = null;

    /**
     * @Serializer\SerializedName("DOB")
     * @Serializer\Groups({"PreciseID"})
     * @var string
     */
    protected $dob = null;

    /**
     * @Serializer\SerializedName("YOB")
     * @Serializer\Groups({"PreciseID"})
     * @var int
     */
    protected $yob = null;

    /**
     * @Serializer\SerializedName("MothersMaidenName")
     * @Serializer\Groups({"PreciseID"})
     * @var string
     */
    protected $mothersMaidenName = null;

    /**
     * @Serializer\SerializedName("EmailAddress")
     * @Serializer\Groups({"PreciseID"})
     * @var string
     */
    protected $emailAddress = null;

    /**
     * @return Name
     */
    public function getName()
    {
        if (null == $this->name) {
            $this->name = new Name();
        }
        return $this->name;
    }

    /**
     * @param Name $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getSsn()
    {
        return $this->ssn;
    }

    /**
     * @param int $ssn
     */
    public function setSsn($ssn)
    {
        $this->ssn = $ssn;
    }

    /**
     * @return CurrentAddress
     */
    public function getCurrentAddress()
    {
        if (null == $this->currentAddress) {
            $this->currentAddress = new CurrentAddress();
        }
        return $this->currentAddress;
    }

    /**
     * @param CurrentAddress $currentAddress
     */
    public function setCurrentAddress($currentAddress)
    {
        $this->currentAddress = $currentAddress;
    }

    /**
     * @return PreviousAddress
     */
    public function getPreviousAddress()
    {
        return $this->previousAddress;
    }

    /**
     * @param PreviousAddress $previousAddress
     */
    public function setPreviousAddress(PreviousAddress $previousAddress = null)
    {
        $this->previousAddress = $previousAddress;
    }

    /**
     * @return Phone
     */
    public function getPhone()
    {
        if (null == $this->phone) {
            $this->phone = new Phone();
        }
        return $this->phone;
    }

    /**
     * @param Phone $phone
     */
    public function setPhone(Phone $phone = null)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getEmployment()
    {
        return $this->employment;
    }

    /**
     * @param mixed $employment
     */
    public function setEmployment($employment)
    {
        $this->employment = $employment;
    }

    /**
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @param int $age
     */
    public function setAge($age)
    {
        $this->age = $age;
    }

    /**
     * @return string
     */
    public function getDob()
    {
        return $this->dob;
    }

    /**
     * @param string $dob
     */
    public function setDob($dob)
    {
        $this->dob = $dob;
    }

    /**
     * @return int
     */
    public function getYob()
    {
        return $this->yob;
    }

    /**
     * @param int $yob
     */
    public function setYob($yob)
    {
        $this->yob = $yob;
    }

    /**
     * @return string
     */
    public function getMothersMaidenName()
    {
        return $this->mothersMaidenName;
    }

    /**
     * @param string $mothersMaidenName
     */
    public function setMothersMaidenName($mothersMaidenName)
    {
        $this->mothersMaidenName = $mothersMaidenName;
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param string $emailAddress
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }
}
