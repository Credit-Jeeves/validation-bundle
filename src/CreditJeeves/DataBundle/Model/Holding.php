<?php
namespace CreditJeeves\DataBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class Holding
{
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
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\User",
     *     mappedBy="holding",
     *     cascade={
     *         "remove",
     *         },
     *     orphanRemoval=true
     * )
     */
    protected $dealers;

    /**
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     mappedBy="holding",
     *     cascade={
     *         "remove",
     *         },
     *     orphanRemoval=true
     * )
     */
    protected $groups;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->groups = new ArrayCollection();
        $this->dealers = new ArrayCollection();
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
     * Add groups
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $groups
     * @return Holding
     */
    public function addGroup(\CreditJeeves\DataBundle\Entity\Group $groups)
    {
        $this->groups[] = $groups;
    
        return $this;
    }

    /**
     * Remove groups
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $groups
     */
    public function removeGroup(\CreditJeeves\DataBundle\Entity\Group $groups)
    {
        $this->groups->removeElement($groups);
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
        $this->dealers[] = $dealer;
    
        return $this;
    }

    /**
     * Remove dealer
     *
     * @param \CreditJeeves\DataBundle\Entity\Dealer $dealer
     */
    public function removeDealer(\CreditJeeves\DataBundle\Entity\Dealer $dealer)
    {
        $this->dealers->removeElement($dealer);
    }

    /**
     * Get dealers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDealers()
    {
        return $this->dealers;
    }
}
