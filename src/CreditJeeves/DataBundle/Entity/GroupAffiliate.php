<?php

namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Enum\UserCulture;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * GroupAffiliate
 *
 * @ORM\Table(name="cj_account_group_affiliate")
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\GroupAffiliateRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @deprecated Not in use?
 */
class GroupAffiliate
{
    /**
     * @var bigint
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="cj_account_group_id", type="bigint")
     */
    protected $groupId;

    /**
     * @var integer
     *
     * @ORM\Column(name="cj_account_id", type="bigint")
     */
    protected $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="website_url", type="string", length=255)
     */
    protected $websiteUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="auth_token", type="string", length=255)
     */
    protected $authToken;

    /**
     * @var string
     *
     * @ORM\Column(name="external_key", type="string", length=255)
     */
    protected $externalKey;

    /**
     * @var UserCulture
     *
     * @ORM\Column(name="culture", type="UserCulture", options={"default"="en"})
     */
    protected $culture = UserCulture::EN;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\Group", inversedBy="group_affilate")
     * @ORM\JoinColumn(name="cj_account_group_id", referencedColumnName="id")
     * @Assert\Type(type="CreditJeeves\DataBundle\Entity\Group")
     */
    protected $group;

    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\User", inversedBy="group_affilate")
     * @ORM\JoinColumn(name="cj_account_id", referencedColumnName="id")
     * @Assert\Type(type="CreditJeeves\DataBundle\Entity\User")
     * @Assert\Valid()
     */
    protected $user;

    /**
     * Get id
     *
     * @return bigint
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set groupId
     *
     * @param integer $groupId
     * @return GroupAffiliate
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * Get groupId
     *
     * @return integer
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return GroupAffiliate
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set websiteUrl
     *
     * @param string $websiteUrl
     * @return GroupAffiliate
     */
    public function setWebsiteUrl($websiteUrl)
    {
        $this->websiteUrl = $websiteUrl;

        return $this;
    }

    /**
     * Get websiteUrl
     *
     * @return string
     */
    public function getWebsiteUrl()
    {
        return $this->websiteUrl;
    }

    /**
     * Set authToken
     *
     * @param string $authToken
     * @return GroupAffiliate
     */
    public function setAuthToken($authToken)
    {
        $this->authToken = $authToken;

        return $this;
    }

    /**
     * Get authToken
     *
     * @return string
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }

    /**
     * Set externalKey
     *
     * @param string $externalKey
     * @return GroupAffiliate
     */
    public function setExternalKey($externalKey)
    {
        $this->externalKey = $externalKey;

        return $this;
    }

    /**
     * Get externalKey
     *
     * @return string
     */
    public function getExternalKey()
    {
        return $this->externalKey;
    }

    /**
     * Set culture
     *
     * @param UserCulture $culture
     * @return GroupAffiliate
     */
    public function setCulture($culture)
    {
        $this->culture = $culture;

        return $this;
    }

    /**
     * Get culture
     *
     * @return UserCulture
     */
    public function getCulture()
    {
        return $this->culture;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return GroupAffiliate
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
     * @return GroupAffiliate
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
    * @param $websiteUrl
    * @param $externalKey
    *
    * @return string
    */
    public static function generateAuthToken($websiteUrl, $externalKey)
    {
        return md5($websiteUrl . $externalKey);
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedValue()
    {
        if (!$this->getExternalKey()) {
            $this->setExternalKey(strtoupper(base_convert(uniqid(), 16, 36)));
        }
 
        if (!$this->getAuthToken()) {
            $this->setAuthToken(self::generateAuthToken($this->getWebsiteUrl(), $this->getExternalKey()));
        }
    }    


    /**
     * Set group
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $group
     * @return GroupAffiliate
     */
    public function setGroup(\CreditJeeves\DataBundle\Entity\Group $group = null)
    {
        $this->group = $group;
    
        return $this;
    }

    /**
     * Get group
     *
     * @return \CreditJeeves\DataBundle\Entity\Group 
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set user
     *
     * @param \CreditJeeves\DataBundle\Entity\User $user
     * @return GroupAffiliate
     */
    public function setUser(\CreditJeeves\DataBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \CreditJeeves\DataBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}