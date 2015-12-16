<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use RentJeeves\DataBundle\Entity\Tenant;
use CreditJeeves\DataBundle\Entity\Holding;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\MappedSuperclass
 * @UniqueEntity(
 *     fields={"holding", "residentId"},
 *     groups={"unique_entity"},
 *     errorPath="residentId",
 *     message="error.residentId.already_use"
 * )
 */
abstract class ResidentMapping
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
     *     targetEntity="RentJeeves\DataBundle\Entity\Tenant",
     *     inversedBy="residentsMapping",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *     name="tenant_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     * @Assert\NotBlank
     * @Serializer\Exclude
     */
    protected $tenant;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Holding",
     *     inversedBy="residentsMapping"
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
     *      name="resident_id",
     *      length=128,
     *      nullable=false
     * )
     * @Assert\NotBlank(
     *     message="common.residentId.required",
     *     groups = {
     *         "import",
     *         "add_or_edit_tenants"
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
    protected $residentId;

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
     * @param string $residentId
     */
    public function setResidentId($residentId)
    {
        $this->residentId = $residentId;
    }

    /**
     * @return string
     */
    public function getResidentId()
    {
        return $this->residentId;
    }

    /**
     * Set Tenant
     *
     * @param Tenant $tenant
     * @return ResidentMapping
     */
    public function setTenant(Tenant $tenant)
    {
        $this->tenant = $tenant;
        return $this;
    }

    /**
     * Get Tenant
     *
     * @return Tenant
     */
    public function getTenant()
    {
        return $this->tenant;
    }

    /**
     * Holding $holding = null - need for sonata
     * @todo: remove after implement autocomplete
     *
     * Set Holding
     *
     * @param Holding $holding
     * @return ResidentMapping
     */
    public function setHolding(Holding $holding = null)
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
}
