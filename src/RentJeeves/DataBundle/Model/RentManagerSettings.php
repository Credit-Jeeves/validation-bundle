<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Entity\Holding;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class RentManagerSettings
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
     * @var integer
     */
    protected $id;

    /**
     * @ORM\Column(
     *     name="corpid",
     *     type="string",
     *     nullable=false
     * )
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $corpid;

    /**
     * @ORM\Column(
     *     name="user",
     *     type="encrypt",
     *     nullable=false
     * )
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $user;

    /**
     * @ORM\Column(
     *     name="password",
     *     type="encrypt",
     *     nullable=false
     * )
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $password;

    /**
     * @ORM\Column(
     *     name="location_id",
     *     type="string",
     *     nullable=false
     * )
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $locationId;

    /**
     * @ORM\OneToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Holding",
     *     inversedBy="rentManagerSettings",
     *     cascade={"persist", "merge"},
     *     orphanRemoval=true
     * )
     * @ORM\JoinColumn(name="holding_id", referencedColumnName="id", nullable=false, unique=true)
     * @var Holding
     */
    protected $holding;

    /**
     * @return string
     */
    public function getCorpid()
    {
        return $this->corpid;
    }

    /**
     * @param string $corpid
     */
    public function setCorpid($corpid)
    {
        $this->corpid = $corpid;
    }

    /**
     * @return Holding
     */
    public function getHolding()
    {
        return $this->holding;
    }

    /**
     * @param Holding $holding
     */
    public function setHolding(Holding $holding)
    {
        $this->holding = $holding;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * @param string $locationId
     */
    public function setLocationId($locationId)
    {
        $this->locationId = $locationId;
    }
}
