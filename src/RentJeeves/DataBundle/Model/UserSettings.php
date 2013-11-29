<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class UserSettings
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean", name="is_base_order_report")
     */
    protected $isBaseOrderReport;

    /**
     * @ORM\OneToOne(targetEntity="\CreditJeeves\DataBundle\Entity\User", inversedBy="settings")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param bool $isBaseOrderReport
     */
    public function setIsBaseOrderReport($isBaseOrderReport)
    {
        $this->isBaseOrderReport = $isBaseOrderReport;
    }

    /**
     * @return bool
     */
    public function getIsBaseOrderReport()
    {
        return $this->isBaseOrderReport;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
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
}
