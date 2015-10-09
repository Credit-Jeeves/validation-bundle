<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\MappedSuperclass
 */
class DebitCardBinlist
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     *
     * @ORM\Column(
     *      name="iin",
     *      type="integer",
     *      nullable=false,
     *      unique=true
     * )
     * @Serializer\Type("integer")
     *
     * @var integer
     */
    protected $iin;

    /**
     * @ORM\Column(
     *      name="card_brand",
     *      type="string",
     *      nullable=true
     * )
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $cardBrand;

    /**
     * @ORM\Column(
     *      name="card_sub_brand",
     *      type="string",
     *      nullable=true
     * )
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $cardSubBrand;

    /**
     * @ORM\Column(
     *      name="card_type",
     *      type="string",
     *      nullable=true
     * )
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $cardType;

    /**
     * @ORM\Column(
     *      name="card_category",
     *      type="string",
     *      nullable=true
     * )
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $cardCategory;

    /**
     * @ORM\Column(
     *      name="country_code",
     *      type="string",
     *      nullable=true
     * )
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $countryCode;

    /**
     * @ORM\Column(
     *      name="bank_name",
     *      type="string",
     *      nullable=true
     * )
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $bankName;

    /**
     * @ORM\Column(
     *      name="bank_url",
     *      type="string",
     *      nullable=true
     * )
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $bankUrl;

    /**
     * @ORM\Column(
     *      name="bank_phone",
     *      type="string",
     *      nullable=true
     * )
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $bankPhone;

    /**
     * @ORM\Column(
     *      name="bank_city",
     *      type="string",
     *      nullable=true
     * )
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $bankCity;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getIin()
    {
        return $this->iin;
    }

    /**
     * @param int $iin
     */
    public function setIin($iin)
    {
        $this->iin = $iin;
    }

    /**
     * @return string
     */
    public function getCardBrand()
    {
        return $this->cardBrand;
    }

    /**
     * @param string $cardBrand
     */
    public function setCardBrand($cardBrand)
    {
        $this->cardBrand = $cardBrand;
    }

    /**
     * @return string
     */
    public function getCardSubBrand()
    {
        return $this->cardSubBrand;
    }

    /**
     * @param string $cardSubBrand
     */
    public function setCardSubBrand($cardSubBrand)
    {
        $this->cardSubBrand = $cardSubBrand;
    }

    /**
     * @return string
     */
    public function getCardType()
    {
        return $this->cardType;
    }

    /**
     * @param string $cardType
     */
    public function setCardType($cardType)
    {
        $this->cardType = $cardType;
    }

    /**
     * @return string
     */
    public function getCardCategory()
    {
        return $this->cardCategory;
    }

    /**
     * @param string $cardCategory
     */
    public function setCardCategory($cardCategory)
    {
        $this->cardCategory = $cardCategory;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * @param string $bankName
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;
    }

    /**
     * @return string
     */
    public function getBankUrl()
    {
        return $this->bankUrl;
    }

    /**
     * @param string $bankUrl
     */
    public function setBankUrl($bankUrl)
    {
        $this->bankUrl = $bankUrl;
    }

    /**
     * @return string
     */
    public function getBankPhone()
    {
        return $this->bankPhone;
    }

    /**
     * @param string $bankPhone
     */
    public function setBankPhone($bankPhone)
    {
        $this->bankPhone = $bankPhone;
    }

    /**
     * @return string
     */
    public function getBankCity()
    {
        return $this->bankCity;
    }

    /**
     * @param string $bankCity
     */
    public function setBankCity($bankCity)
    {
        $this->bankCity = $bankCity;
    }
}
