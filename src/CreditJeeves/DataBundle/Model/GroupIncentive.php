<?php
namespace CreditJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class GroupIncentive
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $cj_group_id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $consecutive_number;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $is_active;

    /**
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * @ORM\Column(type="text")
     */
    protected $text;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;
    
    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="incentives"
     * )
     * @ORM\JoinColumn(
     *     name="cj_group_id",
     *     referencedColumnName="id"
     * )
     */
    protected $group;

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
     * Set cj_group_id
     *
     * @param integer $cjGroupId
     * @return GroupIncentive
     */
    public function setCjGroupId($cjGroupId)
    {
        $this->cj_group_id = $cjGrouptId;
        return $this;
    }

    /**
     * Get cj_group_id
     *
     * @return integer
     */
    public function getCjGroupId()
    {
        return $this->cj_group_id;
    }

    /**
     * consecutive number
     *
     * @param string $ConsecutiveNumber
     * @return GroupIncentive
     */
    public function setConsecutiveNumber($ConsecutiveNumber)
    {
        $this->consecutive_number = $ConsecutiveNumber;
        return $this;
    }

    /**
     * Get score
     *
     * @return string
     */
    public function getConsecutiveNumber()
    {
        return $this->consecutive_number;
    }

    /**
     * title
     *
     * @param string $title
     * @return GroupIncentive
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * text
     *
     * @param string $text
     * @return GroupIncentive
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Get text
     *
     * @return text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return GroupIncentive
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set Group
     *
     * @param Group $group
     * @return GroupIncentive
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
}
