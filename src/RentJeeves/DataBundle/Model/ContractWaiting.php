<?php

namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use \DateTime;

/**
 * @ORM\MappedSuperclass
 */
abstract class ContractWaiting
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Unit",
     *     inversedBy="contractsWaiting",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *     name="unit_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     */
    protected $unit;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Property",
     *     inversedBy="contractsWaiting",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *     name="property_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     */
    protected $property;

    /**
     * @ORM\Column(
     *     type="decimal",
     *     precision=10,
     *     scale=2,
     *     nullable=false
     * )
     * @Assert\NotBlank(
     *     message="error.rent.empty"
     * )
     * @Assert\Regex(
     *     pattern = "/^\d+(\.\d{1,2})?$/"
     * )
     */
    protected $rent;

    /**
     * @ORM\Column(
     *      type="string",
     *      name="resident_id",
     *      length=128,
     *      nullable=false
     * )
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=1,
     *     max=128
     * )
     */
    protected $residentId;

    /**
     * @ORM\Column(
     *     type="decimal",
     *     precision=10,
     *     scale=2,
     *     nullable=false,
     *     name="integrated_balance",
     *     options={
     *          "default":"0.00"
     *     }
     * )
     * @Assert\NotBlank(
     *     message="error.balance.empty"
     * )
     * @Assert\Regex(
     *     pattern = "/^-?\d+(\.\d{1,2})?$/"
     * )
     */
    protected $integratedBalance = 0.00;


    /**
     * @ORM\Column(
     *     name="start_at",
     *     type="date",
     *     nullable=false
     * )
     * @Assert\NotBlank(
     *     message="error.start.empty"
     * )
     */
    protected $startAt;

    /**
     * @ORM\Column(
     *     name="finish_at",
     *     type="date",
     *     nullable=true
     * )
     * @Assert\NotBlank(
     *     message="error.finish.empty"
     * )
     */
    protected $finishAt;
    
    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="created_at",
     *     type="datetime"
     * )
     * @Serializer\Exclude
     */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(
     *     name="updated_at",
     *     type="datetime"
     * )
     * @Serializer\Exclude
     */
    protected $updatedAt;

    /**
     * @ORM\Column(
     *     name="first_name",
     *     type="string",
     *     nullable=false
     * )
     * @Assert\NotBlank(
     *     message="error.user.first_name.empty",
     *     groups={
     *         "import"
     *     }
     * )
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     minMessage="error.user.first_name.short",
     *     maxMessage="error.user.first_name.long",
     *     groups={
     *         "import"
     *     }
     * )
     * @Assert\Regex(
     *     pattern = "/^[a-zA-Z \-'\s]{2,65}$/",
     *     message="regexp.error.name",
     *     groups = {
     *         "import"
     *     }
     * )
     * @Serializer\Groups({"RentJeevesImport"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $firstName;

    /**
     * @ORM\Column(
     *     name="last_name",
     *     type="string",
     *     nullable=false
     * )
     * @Assert\NotBlank(
     *     message="error.user.last_name.empty",
     *     groups={
     *         "import"
     *     }
     * )
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     minMessage="error.user.last_name.short",
     *     maxMessage="error.user.last_name.long",
     *     groups={
     *         "import"
     *     }
     * )
     * @Assert\Regex(
     *     pattern = "/^[a-zA-Z \-'\s]{2,65}$/",
     *     message="regexp.error.name",
     *     groups = {
     *         "import"
     *     }
     * )
     * @Serializer\Groups({"RentJeevesImport"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $lastName;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="waitingContracts",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *     name="group_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     * @Serializer\Exclude
     */
    protected $group;

    /**
     * @ORM\Column(
     *     type="PaymentAccepted",
     *     nullable=false,
     *     name="payment_accepted",
     *     options={
     *         "default"="0"
     *     }
     * )
     */
    protected $paymentAccepted = PaymentAccepted::ANY;

    /**
     * @return integer
     */
    public function getPaymentAccepted()
    {
        return $this->paymentAccepted;
    }

    /**
     * @param integer $paymentAccepted
     */
    public function setPaymentAccepted($paymentAccepted)
    {
        $this->paymentAccepted = $paymentAccepted;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param Property $property
     */
    public function setProperty(Property $property)
    {
        $this->property = $property;
    }

    /**
     * @return Property
     */
    public function getProperty()
    {
        return $this->property;
    }


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
     * @param DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $finishAt
     */
    public function setFinishAt($finishAt)
    {
        $this->finishAt = $finishAt;
    }

    /**
     * @return DateTime
     */
    public function getFinishAt()
    {
        return $this->finishAt;
    }

    /**
     * @param float $integratedBalance
     */
    public function setIntegratedBalance($integratedBalance)
    {
        $this->integratedBalance = $integratedBalance;
    }

    /**
     * @return float
     */
    public function getIntegratedBalance()
    {
        return $this->integratedBalance;
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
    public function getRent()
    {
        return $this->rent;
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
    public function getResidentId()
    {
        return $this->residentId;
    }

    /**
     * @param DateTime $startAt
     */
    public function setStartAt($startAt)
    {
        $this->startAt = $startAt;
    }

    /**
     * @return DateTime
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * @param Unit $unit
     */
    public function setUnit(Unit $unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return Unit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param Group $group
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }
}
