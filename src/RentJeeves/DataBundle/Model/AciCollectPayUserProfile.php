<?php

namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use RentJeeves\DataBundle\Entity\AciCollectPayProfileBilling;

/**
 * @ORM\MappedSuperclass
 */
abstract class AciCollectPayUserProfile
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\OneToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\User",
     *     inversedBy="aciCollectPayProfile"
     * )
     */
    protected $user;

    /**
     * @var int
     *
     * @ORM\Column(
     *     name="profile_id",
     *     type="integer",
     *     nullable=false
     * )
     */
    protected $profileId;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="created_at",
     *     type="datetime"
     * )
     */
    protected $createdAt;

    /**
     * @var ArrayCollection|AciCollectPayProfileBilling[]
     *
     * @ORM\OneToMany(
     *      targetEntity="RentJeeves\DataBundle\Entity\AciCollectPayProfileBilling",
     *      mappedBy="profile",
     *      cascade={"all"}
     * )
     */
    protected $aciCollectPayProfileBillings;

    public function __construct()
    {
        $this->aciCollectPayProfileBillings = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param int $profileId
     */
    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
    }

    /**
     * @return int
     */
    public function getProfileId()
    {
        return $this->profileId;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return ArrayCollection|AciCollectPayProfileBilling[]
     */
    public function getAciCollectPayProfileBillings()
    {
        return $this->aciCollectPayProfileBillings;
    }

    /**
     * @param AciCollectPayProfileBilling[] $aciCollectPayProfileBillings
     */
    public function addAciCollectPayProfileBilling(AciCollectPayProfileBilling $aciCollectPayProfileBilling)
    {
        $this->aciCollectPayProfileBillings->add($aciCollectPayProfileBilling);
    }

    /**
     * @param AciCollectPayProfileBilling $aciCollectPayProfileBilling
     */
    public function removeAciCollectPayProfileBilling(AciCollectPayProfileBilling $aciCollectPayProfileBilling)
    {
        $this->aciCollectPayProfileBillings->removeElement($aciCollectPayProfileBilling);
    }
}
