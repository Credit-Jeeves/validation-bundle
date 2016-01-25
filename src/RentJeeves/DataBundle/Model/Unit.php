<?php
namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Entity\ContractWaiting as ContractWaitingEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\DataBundle\Entity\Group;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
abstract class Unit
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"RentJeevesImport", "payRent", "AdminUnit"})
     */
    protected $id;

    /**
     * @ORM\Column(
     *     name="name",
     *     type="string",
     *     length=50
     * )
     * @Assert\Regex(
     *     message="error.unit.regexp",
     *     pattern = "/^[A-Za-z_0-9\-\.\/]{1,50}$/",
     *     groups = {
     *         "import",
     *         "registration_tos",
     *         "landlordImport"
     *     }
     * )
     * @Serializer\Groups({"RentJeevesImport", "payRent"})
     * @Serializer\Accessor(getter="getName",setter="setName")
     */
    protected $name;

    /**
     * @ORM\Column(
     *     type="integer",
     *     nullable=true
     * )
     */
    protected $rent;

    /**
     * @ORM\Column(
     *     type="integer",
     *     nullable=true
     * )
     */
    protected $beds;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Property",
     *     inversedBy="units",
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
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Holding",
     *     inversedBy="units"
     * )
     * @ORM\JoinColumn(
     *     name="holding_id",
     *     referencedColumnName="id"
     * )
     */
    protected $holding;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="units"
     * )
     * @ORM\JoinColumn(
     *     name="group_id",
     *     referencedColumnName="id"
     * )
     */
    protected $group;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="created_at",
     *     type="datetime"
     * )
     */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(
     *     name="updated_at",
     *     type="datetime"
     * )
     */
    protected $updatedAt;

    /**
     * @ORM\Column(
     *      name="deleted_at",
     *      type="datetime",
     *      nullable=true
     * )
     */
    protected $deletedAt;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\Contract",
     *     mappedBy="unit",
     *     cascade={
     *         "persist",
     *         "merge"
     *     },
     *     orphanRemoval=true
     * )
     *
     * @var ArrayCollection
     */
    protected $contracts;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\ContractWaiting",
     *     mappedBy="unit",
     *     cascade={
     *       "persist"
     *     }
     * )
     *
     * @var ArrayCollection
     */
    protected $contractsWaiting;

    /**
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\UnitMapping",
     *     mappedBy="unit",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $unitMapping;

    public function __construct()
    {
        $this->contracts = new ArrayCollection();
        $this->contractsWaiting = new ArrayCollection();
    }

    /**
     * @param ContractWaitingEntity $contractsWaiting
     */
    public function addContractsWaiting(ContractWaitingEntity $contractsWaiting)
    {
        $this->contractsWaiting = $contractsWaiting;
    }

    /**
     * @return ArrayCollection|\RentJeeves\DataBundle\Entity\ContractWaiting[]
     */
    public function getContractsWaiting()
    {
        return $this->contractsWaiting;
    }

    /**
     * @param mixed $deletedAt
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }

    /**
     * @return mixed
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
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
     * Set name
     *
     * @param string $name
     * @return Unit
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set rent
     *
     * @param double $rent
     * @return Unit
     */
    public function setRent($rent)
    {
        $this->rent = $rent;

        return $this;
    }

    /**
     * Get rent
     *
     * @return double
     */
    public function getRent()
    {
        return $this->rent;
    }

    /**
     * Set beds
     *
     * @param integer $beds
     * @return Unit
     */
    public function setBeds($beds)
    {
        $this->beds = $beds;

        return $this;
    }

    /**
     * Get beds
     *
     * @return integer
     */
    public function getBeds()
    {
        return $this->beds;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Address
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

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Unit
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set Property
     *
     * @param Property $property
     * @return Unit
     */
    public function setProperty(Property $property = null)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * Get Property
     *
     * @return Property
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Set Holding
     *
     * @param Holding $holding
     * @return Unit
     */
    public function setHolding(Holding $holding = null)
    {
        $this->holding = $holding;

        return $this;
    }

    /**
     * Get Holding
     *
     * @return Holding
     */
    public function getHolding()
    {
        return $this->holding;
    }

    /**
     * Set Group
     *
     * @param Group $group
     * @return Unit
     */
    public function setGroup(Group $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get Group
     *
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Add Contract
     *
     * @param Contract $contract
     * @return $this
     */
    public function addContract(Contract $contract)
    {
        $this->contracts[] = $contract;

        return $this;
    }

    /**
     * Remove Contract
     *
     * @param Contract
     * @return $this
     */
    public function removeContract(Contract $contract)
    {
        $this->contracts->removeElement($contract);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getContracts()
    {
        return $this->contracts;
    }

    /**
     * Add ContractWaiting
     *
     * @param ContractWaitingEntity $contract
     * @return $this
     */
    public function addContractWaiting(ContractWaitingEntity $contract)
    {
        $this->contractsWaiting[] = $contract;

        return $this;
    }

    /**
     * Remove ContractWaiting
     *
     * @param ContractWaitingEntity Contract
     * @return $this
     */
    public function removeContractWaiting(ContractWaitingEntity $contract)
    {
        if ($this->contractsWaiting->contains($contract)) {
            $this->contractsWaiting->removeElement($contract);
        }

        return $this;
    }

    /**
     * @param UnitMapping $unitMapping
     */
    public function setUnitMapping(UnitMapping $unitMapping)
    {
        $this->unitMapping = $unitMapping;
    }

    /**
     * @return UnitMapping
     */
    public function getUnitMapping()
    {
        return $this->unitMapping;
    }

    /**
     * Sometimes unit name can be empty if the property is single, so they should be checked together.
     *
     * @Assert\True(
     *     message="error.unit.empty",
     *     groups={
     *         "import"
     *     }
     * )
     */
    public function isValidName()
    {
        if (!empty($this->name)) {
            return true;
        }

        $property = $this->getProperty();
        if ($property && $property->getPropertyAddress() && $property->getPropertyAddress()->isSingle()) {
            return true;
        }

        return false;
    }
}
