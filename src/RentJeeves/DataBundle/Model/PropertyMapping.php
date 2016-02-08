<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use CreditJeeves\DataBundle\Entity\Holding;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\Property as PropertyEntity;

/**
 * @ORM\MappedSuperclass
 */
abstract class PropertyMapping
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Property",
     *     inversedBy="propertyMapping"
     * )
     * @ORM\JoinColumn(
     *     name="property_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     * @Assert\NotBlank
     * @Serializer\Exclude
     */
    protected $property;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Holding",
     *     inversedBy="propertyMapping"
     * )
     * @ORM\JoinColumn(
     *     name="holding_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     * @Assert\NotBlank
     * @Serializer\Exclude
     */
    protected $holding;

    /**
     * @ORM\Column(
     *      type="string",
     *      name="external_property_id",
     *      length=128,
     *      nullable=false
     * )
     * @Assert\NotBlank(
     *     groups = {
     *         "import"
     *     }
     * )
     * @Assert\Length(
     *     min=1,
     *     max=128,
     *     groups = {
     *         "import"
     *     }
     * )
     * @Assert\Regex(
     *     pattern = "/^[A-Za-z_0-9\-]{1,128}$/",
     *     message = "import.error.propertyId",
     *     groups = {
     *         "import"
     *     }
     * )
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $externalPropertyId;

    /**
     * @param string $externalPropertyId
     */
    public function setExternalPropertyId($externalPropertyId)
    {
        $this->externalPropertyId = $externalPropertyId;
    }

    /**
     * @return string
     */
    public function getExternalPropertyId()
    {
        return $this->externalPropertyId;
    }

    /**
     * @param PropertyEntity $property
     */
    public function setProperty(PropertyEntity $property)
    {
        $this->property = $property;
    }

    /**
     * @return PropertyEntity
     */
    public function getProperty()
    {
        return $this->property;
    }

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
     * Set Holding
     *
     * @param Holding $holding
     * @return ResidentMapping
     */
    public function setHolding(Holding $holding)
    {
        $this->holding = $holding;

        return $this;
    }

    /**
     * Get Holding
     *
     * @return Holding
     */
    public function getHolding()
    {
        return $this->holding;
    }

    /**
     * @Assert\True(
     *     message="admin.error.holding_does_not_have_property",
     *     groups={"admin_part"}
     * )
     */
    public function isHoldingContainsProperty()
    {
        foreach ($this->getProperty()->getPropertyGroups() as $group) {
            if ($group->getHolding() === $this->getHolding()) {
                return true;
            }
        }

        return false;
    }
}
