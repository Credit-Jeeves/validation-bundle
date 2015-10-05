<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
class DebitCardDurbin
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(
     *      name="frb_id",
     *      type="string",
     *      nullable=true
     * )
     *
     * @var string
     */
    protected $frbId;

    /**
     * @ORM\Column(
     *      name="short_name",
     *      type="string",
     *      nullable=true
     * )
     *
     * @var string
     */
    protected $shortName;

    /**
     * @ORM\Column(
     *      name="city",
     *      type="string",
     *      nullable=true
     * )
     *
     * @var string
     */
    protected $city;

    /**
     * @ORM\Column(
     *      name="state",
     *      type="string",
     *      nullable=true
     * )
     *
     * @var string
     */
    protected $state;

    /**
     * @ORM\Column(
     *      name="type",
     *      type="string",
     *      nullable=true
     * )
     *
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(
     *      name="fdic_id",
     *      type="string",
     *      nullable=true
     * )
     *
     * @var string
     */
    protected $fdicId;

    /**
     * @ORM\Column(
     *      name="ots_id",
     *      type="string",
     *      nullable=true
     * )
     *
     * @var string
     */
    protected $otsId;

    /**
     * @ORM\Column(
     *      name="ncua_id",
     *      type="string",
     *      nullable=true
     * )
     *
     * @var string
     */
    protected $ncuaId;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getFrbId()
    {
        return $this->frbId;
    }

    /**
     * @param string $frbId
     */
    public function setFrbId($frbId)
    {
        $this->frbId = $frbId;
    }

    /**
     * @return string
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @param string $shortName
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getFdicId()
    {
        return $this->fdicId;
    }

    /**
     * @param string $fdicId
     */
    public function setFdicId($fdicId)
    {
        $this->fdicId = $fdicId;
    }

    /**
     * @return string
     */
    public function getOtsId()
    {
        return $this->otsId;
    }

    /**
     * @param string $otsId
     */
    public function setOtsId($otsId)
    {
        $this->otsId = $otsId;
    }

    /**
     * @return string
     */
    public function getNcuaId()
    {
        return $this->ncuaId;
    }

    /**
     * @param string $ncuaId
     */
    public function setNcuaId($ncuaId)
    {
        $this->ncuaId = $ncuaId;
    }
}
