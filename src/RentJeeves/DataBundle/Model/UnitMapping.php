<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Entity\Unit as UnitEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\MappedSuperclass
 */
abstract class UnitMapping
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $id;

    /**
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Unit",
     *     inversedBy="unitMapping",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *     name="unit_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     */
    protected $unit;

    /**
     * @ORM\Column(
     *      type="string",
     *      name="external_unit_id",
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
     * @Serializer\Groups({"RentJeevesImport"})
     */
    protected $externalUnitId;

    /**
     * @param string $externalUnitId
     */
    public function setExternalUnitId($externalUnitId)
    {
        $this->externalUnitId = $externalUnitId;
    }

    /**
     * @return string
     */
    public function getExternalUnitId()
    {
        return $this->externalUnitId;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param UnitEntity $unit
     */
    public function setUnit(UnitEntity $unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return UnitEntity|null
     */
    public function getUnit()
    {
        return $this->unit;
    }
}
