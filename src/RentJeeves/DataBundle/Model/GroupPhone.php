<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
abstract class GroupPhone
{
    /**
     * @ORM\Column(
     *     name="id",
     *     type="bigint"
     * )
     * @ORM\Id
     * @ORM\GeneratedValue(
     *     strategy="AUTO"
     * )
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="groupPhones",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *     name="group_id",
     *     referencedColumnName="id"
     * )
     * @Serializer\Exclude
     */
    protected $group;

    /**
     * @ORM\Column(
     *     name="phone",
     *     type="string",
     *     length=50
     * )
     * @Assert\Regex(
     *     pattern = "/^(\(\d{3}\)\d{3}-|\d{3}\.\d{3}\.|\d{3}-?\d{3}-?)\d{4}$/",
     *     message="error.phone.format"
     * )
     */
    protected $phone;
    

    /**
     * @ORM\Column(
     *     name="is_active",
     *     type="boolean"
     * )
     */
    protected $isActive = false;

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
     * @return GroupPhone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set is Active
     *
     * @param boolean $isActive
     * @return GroupPhone
     */
    public function setIsActive($isActive = true)
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * Get Is Active
     *
     * @return GroupPhone
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set Group
     *
     * @param Group $group
     * @return GroupPhone
     */
    public function setGroup(\CreditJeeves\DataBundle\Entity\Group $group = null)
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return GroupPhone
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
     * @return GroupPhone
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
     * Need for sonata
     * @todo: Group should be required in db
     *
     * @Assert\True(message = "Group is invalid or empty", groups={"sonata"})
     */
    public function isValidHolding()
    {
        if ($this->getGroup() === null) {
            return false;
        }

        return true;
    }
}
