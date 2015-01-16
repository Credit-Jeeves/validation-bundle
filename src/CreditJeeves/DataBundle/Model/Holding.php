<?php
namespace CreditJeeves\DataBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Entity\AccountingSettings;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\ResManSettings;
use RentJeeves\DataBundle\Model\YardiSettings;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\MappedSuperclass
 */
abstract class Holding
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank(
     *     groups={
     *         "holding"
     *     }
     * )
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     groups={
     *         "holding",
     *     }
     * )
     */
    protected $name;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;

    /**
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\User",
     *     mappedBy="holding",
     *     cascade={"remove"},
     *     orphanRemoval=true
     * )
     */
    protected $users;

    /**
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     mappedBy="holding",
     *     cascade={"remove", "persist"},
     *     orphanRemoval=true
     * )
     */
    protected $groups;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\Unit",
     *     mappedBy="holding",
     *     cascade={"remove", "persist"},
     *     orphanRemoval=true
     * )
     */
    protected $units;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\Contract",
     *     mappedBy="holding",
     *     cascade={
     *         "persist",
     *         "remove",
     *         "merge"
     *     },
     *     orphanRemoval=true
     * )
     */
    protected $contracts;


    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\ResidentMapping",
     *     mappedBy="holding",
     *     cascade={
     *         "persist",
     *         "remove",
     *         "merge"
     *     },
     *     orphanRemoval=true
     * )
     *
     * @var ArrayCollection
     */
    protected $residentsMapping;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\PropertyMapping",
     *     mappedBy="holding",
     *     cascade={
     *         "persist",
     *         "remove",
     *         "merge"
     *     },
     *     orphanRemoval=true
     * )
     * @Serializer\Exclude
     */
    protected $propertyMapping;

    /**
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\YardiSettings",
     *     mappedBy="holding",
     *     cascade={"persist", "remove", "merge"},
     *     fetch="EAGER"
     * )
     */
    protected $yardiSettings;

    /**
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\ResManSettings",
     *     mappedBy="holding",
     *     cascade={"persist", "remove", "merge"},
     *     fetch="EAGER"
     * )
     */
    protected $resManSettings;

    /**
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\AccountingSettings",
     *     mappedBy="holding",
     *     cascade={"persist", "remove", "merge"},
     *     fetch="EAGER"
     * )
     */
    protected $accountingSettings;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->groups = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->units = new ArrayCollection();
        $this->contracts = new ArrayCollection();
        $this->residentsMapping = new ArrayCollection();
    }

    /**
     * @return AccountingSettings
     */
    public function getAccountingSettings()
    {
        return $this->accountingSettings;
    }

    /**
     * @param AccountingSettings $accountingSettings
     */
    public function setAccountingSettings(AccountingSettings $accountingSettings)
    {
        $this->accountingSettings = $accountingSettings;
        $accountingSetting = $this->getAccountingSettings();
        $accountingSetting->setHolding($this);

        return $this;
    }

    /**
     * @return ResManSettings
     */
    public function getResManSettings()
    {
        return $this->resManSettings;
    }

    /**
     * @param ResManSettings $resManSettings
     */
    public function setResManSettings(ResManSettings $resManSettings = null)
    {
        $this->resManSettings = $resManSettings;
    }

    /**
     * @param YardiSettings $yardiSettings
     */
    public function setYardiSettings(YardiSettings $yardiSettings = null)
    {
        $this->yardiSettings = $yardiSettings;
    }

    /**
     * @return YardiSettings
     */
    public function getYardiSettings()
    {
        return $this->yardiSettings;
    }

    /**
     * @param PropertyMapping $propertyMapping
     */
    public function setPropertyMapping(PropertyMapping $propertyMapping)
    {
        $this->propertyMapping = $propertyMapping;
    }

    /**
     * @return PropertyMapping
     */
    public function getPropertyMapping()
    {
        return $this->propertyMapping;
    }

    /**
     * @param ResidentMapping $resident
     */
    public function addResidentsMapping(ResidentMapping $resident)
    {
        $this->residentsMapping[] = $resident;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getResidentsMapping()
    {
        return $this->residentsMapping;
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
     * @return Holding
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Holding
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
     * @return Holding
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
     * Add group
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $group
     * @return Holding
     */
    public function addGroup(\CreditJeeves\DataBundle\Entity\Group $group)
    {
        $this->groups[] = $group;
        return $this;
    }

    /**
     * Remove group
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $group
     */
    public function removeGroup(\CreditJeeves\DataBundle\Entity\Group $group)
    {
        $this->groups->removeElement($group);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Add dealer
     *
     * @param \CreditJeeves\DataBundle\Entity\Dealer $dealer
     * @return Holding
     */
    public function addDealer(\CreditJeeves\DataBundle\Entity\Dealer $dealer)
    {
        $this->users[] = $dealer;
        return $this;
    }

    /**
     * Remove dealer
     *
     * @param \CreditJeeves\DataBundle\Entity\Dealer $dealer
     */
    public function removeDealer(\CreditJeeves\DataBundle\Entity\Dealer $dealer)
    {
        $this->users->removeElement($dealer);
    }

    /**
     * Get dealers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDealers()
    {
        return $this->users;
    }

    /**
     * Add unit
     *
     * @param \RentJeeves\DataBundle\Entity\Unit $unit
     * @return Holding
     */
    public function addUnit(\RentJeeves\DataBundle\Entity\Unit $unit)
    {
        $this->units[] = $unit;
        return $this;
    }

    /**
     * Remove unit
     *
     * @param \RentJeeves\DataBundle\Entity\Unit $unit
     */
    public function removeUnit(\RentJeeves\DataBundle\Entity\Unit $unit)
    {
        $this->units->removeElement($unit);
    }

    /**
     * Get units
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUnits()
    {
        return $this->units;
    }

    /**
     * Add Contract
     *
     * @param \RentJeeves\DataBundle\Entity\Contract $contract
     * @return Holding
     */
    public function addContract(\RentJeeves\DataBundle\Entity\Contract $contract)
    {
        $this->contracts[] = $contract;
        return $this;
    }

    /**
     * Remove Contract
     *
     * @param Contract
     */
    public function removeContract(\RentJeeves\DataBundle\Entity\Contract $contract)
    {
        $this->contracts->removeElement($contract);
    }

    /**
     * Get contracts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContracts()
    {
        return $this->contracts;
    }
}
