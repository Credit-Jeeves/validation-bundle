<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\CoreBundle\Services\AddressLookup\Model\Address;
use Symfony\Component\Validator\Constraints as Assert;

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
     *
     * @Assert\NotBlank(message="api.errors.property.state.empty", groups={"new_unit"})
     *
     * @Serializer\Groups({"payRent"})
     * @Serializer\SerializedName("area")
     */
    protected $state;

    /**
     * @ORM\Column(type="string",length=255)
     *
     * @Assert\NotBlank(message="api.errors.property.city.empty", groups={"new_unit"})
     *
     * @Serializer\Groups({"payRent"})
     */
    protected $city;

    /**
     * @ORM\Column(type="string",length=255)
     *
     * @Assert\NotBlank(message="api.errors.property.street.empty", groups={"new_unit"})
     *
     * @Serializer\Groups({"payRent"})
     */
    protected $street;

    /**
     * @ORM\Column(type="string",length=255)
     *
     * @Assert\NotBlank(message="api.errors.property.number.empty", groups={"new_unit"})
     *
     * @Serializer\Groups({"payRent"})
     */
    protected $number;

    /**
     * @ORM\Column(type="string",length=15)
     *
     * @Assert\NotBlank(message="api.errors.property.zip.empty", groups={"new_unit"})
     *
     * @Serializer\Groups({"payRent"})
     */
    protected $zip;

    /**
     * @ORM\Column(name="google_reference", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"payRent"})
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
     *
     * @Serializer\Groups({"payRent"})
     */
    protected $isSingle;

    /**
     * @ORM\Column(name="ss_lat", type="float", nullable=true)
     */
    protected $lat;

    /**
     * @ORM\Column(name="ss_long", type="float", nullable=true)
     */
    protected $long;

    /**
     * @ORM\Column(name="ss_index", type="string", length=255, unique=true, nullable=true)
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
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param string $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * @return string
     */
    public function getGoogleReference()
    {
        return $this->googleReference;
    }

    /**
     * @param string $googleReference
     */
    public function setGoogleReference($googleReference)
    {
        $this->googleReference = $googleReference;
    }

    /**
     * @return string
     */
    public function getJb()
    {
        return $this->jb;
    }

    /**
     * @param string $jb
     */
    public function setJb($jb)
    {
        $this->jb = $jb;
    }

    /**
     * @return string
     */
    public function getKb()
    {
        return $this->kb;
    }

    /**
     * @param string $kb
     */
    public function setKb($kb)
    {
        $this->kb = $kb;
    }

    /**
     * @return boolean
     */
    public function isSingle()
    {
        return $this->isSingle;
    }

    /**
     * @deprecated Not deprecated,
     * but should set this with using
     * "property.manager"->setupSingleProperty() or "property.manager"->setupNotSingleProperty().
     *
     * @param boolean $isSingle
     */
    public function setIsSingle($isSingle)
    {
        $this->isSingle = $isSingle;
    }

    /**
     * @return string
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @param string $lat
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    }

    /**
     * @return string
     */
    public function getLong()
    {
        return $this->long;
    }

    /**
     * @param string $long
     */
    public function setLong($long)
    {
        $this->long = $long;
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param string $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return sprintf('%s %s', $this->number, $this->street);
    }

    /**
     * @return string
     */
    public function getFullAddress()
    {
        return sprintf('%s, %s, %s %s', $this->getAddress(), $this->city, $this->state, $this->zip);
    }

    /**
     * @param Address $address
     */
    public function setAddressFields(Address $address)
    {
        $this->setState($address->getState());
        $this->setCity($address->getCity());
        $this->setStreet($address->getStreet());
        $this->setNumber($address->getNumber());
        $this->setZip($address->getZip());
        if ($this->getJb() === null && $this->getKb() === null && $address->getJb() && $address->getKb()) {
            $this->setJb($address->getJb());
            $this->setKb($address->getKb());
        } elseif ($address->getLatitude() && $address->getLongitude() && $address->getIndex()) {
            $this->setLat($address->getLatitude());
            $this->setLong($address->getLongitude());
            $this->setIndex($address->getIndex());
        }
    }
}
