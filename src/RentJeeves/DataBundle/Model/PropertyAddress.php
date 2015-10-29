<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
class PropertyAddress
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string",length=255)
     */
    protected $state;

    /**
     * @ORM\Column(type="string",length=255)
     */
    protected $city;

    /**
     * @ORM\Column(type="string",length=255)
     */
    protected $street;

    /**
     * @ORM\Column(type="string",length=255)
     */
    protected $number;

    /**
     * @ORM\Column(type="string",length=15)
     */
    protected $zip;

    /**
     * @ORM\Column(name="google_reference", type="string", length=255, nullable=true)
     */
    protected $googleReference;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $jb;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $kb;

    /**
     * @ORM\Column(name="is_single", type="boolean", nullable=true)
     */
    protected $isSingle;

    /**
     * @ORM\Column(name="ss_lat", type="string", length=255, nullable=true)
     */
    protected $lat;

    /**
     * @ORM\Column(name="ss_long", type="string", length=255, nullable=true)
     */
    protected $long;

    /**
     * @ORM\Column(name="ss_index", type="string", length=255, nullable=true)
     */
    protected $index;

    /**
     * @Gedmo\Timestampable(on="create")
     *
     * @ORM\Column(name="created_at",type="datetime")
     */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     *
     * @ORM\Column(name="updated_at",type="datetime")
     */
    protected $updatedAt;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param mixed $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param mixed $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return mixed
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param mixed $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * @return mixed
     */
    public function getGoogleReference()
    {
        return $this->googleReference;
    }

    /**
     * @param mixed $googleReference
     */
    public function setGoogleReference($googleReference)
    {
        $this->googleReference = $googleReference;
    }

    /**
     * @return mixed
     */
    public function getJb()
    {
        return $this->jb;
    }

    /**
     * @param mixed $jb
     */
    public function setJb($jb)
    {
        $this->jb = $jb;
    }

    /**
     * @return mixed
     */
    public function getKb()
    {
        return $this->kb;
    }

    /**
     * @param mixed $kb
     */
    public function setKb($kb)
    {
        $this->kb = $kb;
    }

    /**
     * @return mixed
     */
    public function getIsSingle()
    {
        return $this->isSingle;
    }

    /**
     * @param mixed $isSingle
     */
    public function setIsSingle($isSingle)
    {
        $this->isSingle = $isSingle;
    }

    /**
     * @return mixed
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @param mixed $lat
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    }

    /**
     * @return mixed
     */
    public function getLong()
    {
        return $this->long;
    }

    /**
     * @param mixed $long
     */
    public function setLong($long)
    {
        $this->long = $long;
    }

    /**
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param mixed $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }
}
