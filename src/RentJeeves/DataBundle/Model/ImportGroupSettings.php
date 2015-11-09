<?php

namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\DataBundle\Enum\ImportType;

/**
 * @ORM\MappedSuperclass
 */
abstract class ImportGroupSettings
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="importSettings"
     * )
     * @ORM\JoinColumn(
     *      name="group_id",
     *      referencedColumnName="id",
     *      nullable=false,
     *      unique=true
     * )
     *
     * @var Group
     */
    protected $group;

    /**
     * @var string
     * @see \RentJeeves\DataBundle\Enum\ImportSource
     *
     * @ORM\Column(
     *     nullable=false,
     *     type="ImportSource",
     *     options={
     *         "default"="csv"
     *     }
     * )
     */
    protected $source = ImportSource::CSV;

    /**
     * @var string
     * @see \RentJeeves\DataBundle\Enum\ImportType
     *
     * @ORM\Column(
     *     name="import_type",
     *     nullable=false,
     *     type="ImportType",
     *     options={
     *         "default"="single_property"
     *     }
     * )
     */
    protected $importType = ImportType::SINGLE_PROPERTY;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="csv_field_delimiter",
     *     nullable=false,
     *     type="string",
     *     options={
     *         "default"=","
     *     }
     * )
     */
    protected $csvFieldDelimiter = ',';

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="csv_text_delimiter",
     *     nullable=false,
     *     type="string",
     *     options={
     *         "default"=""""
     *     }
     * )
     */
    protected $csvTextDelimiter = '"';

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="csv_date_format",
     *     nullable=false,
     *     type="string",
     *     options={
     *         "default"="m/d/Y"
     *     }
     * )
     */
    protected $csvDateFormat = 'm/d/Y';

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="api_property_ids",
     *     nullable=true,
     *     type="string"
     * )
     */
    protected $apiPropertyIds;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param Group $group
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getImportType()
    {
        return $this->importType;
    }

    /**
     * @param string $importType
     */
    public function setImportType($importType)
    {
        $this->importType = $importType;
    }

    /**
     * @return string
     */
    public function getCsvFieldDelimiter()
    {
        return $this->csvFieldDelimiter;
    }

    /**
     * @param string $csvFieldDelimiter
     */
    public function setCsvFieldDelimiter($csvFieldDelimiter)
    {
        $this->csvFieldDelimiter = $csvFieldDelimiter;
    }

    /**
     * @return string
     */
    public function getCsvTextDelimiter()
    {
        return $this->csvTextDelimiter;
    }

    /**
     * @param string $csvTextDelimiter
     */
    public function setCsvTextDelimiter($csvTextDelimiter)
    {
        $this->csvTextDelimiter = $csvTextDelimiter;
    }

    /**
     * @return string
     */
    public function getCsvDateFormat()
    {
        return $this->csvDateFormat;
    }

    /**
     * @param string $csvDateFormat
     */
    public function setCsvDateFormat($csvDateFormat)
    {
        $this->csvDateFormat = $csvDateFormat;
    }

    /**
     * @return string
     */
    public function getApiPropertyIds()
    {
        return $this->apiPropertyIds;
    }

    /**
     * @param string $apiPropertyIds
     */
    public function setApiPropertyIds($apiPropertyIds)
    {
        $this->apiPropertyIds = $apiPropertyIds;
    }
}
