<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\MappedSuperclass
 * @UniqueEntity(
 *     fields={"importSummary", "md5RowContent"},
 *     errorPath="rowContent",
 *     message="error.summary_error.must_be_unique_md5RowContent"
 * )
 */
abstract class ImportError
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
     * @var integer
     *
     * @ORM\Column(
     *      name="exception_uid",
     *      type="string",
     *      nullable=true
     * )
     */
    protected $exceptionUid;

    /**
     * @var ImportSummary
     *
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\ImportSummary",
     *     inversedBy="errors"
     * )
     * @ORM\JoinColumn(
     *     name="import_summary_id",
     *     referencedColumnName="id"
     * )
     */
    protected $importSummary;

    /**
     * @var string
     *
     * @ORM\Column(name="md5_row_content", type="string", length=32)
     */
    protected $md5RowContent;

    /**
     * @var array
     *
     * @ORM\Column(name="row_content", type="json_array")
     */
    protected $rowContent;

    /**
     * @var array
     *
     * @ORM\Column(name="messages", type="json_array")
     */
    protected $messages;

    /**
     * @return int
     */
    public function getExceptionUid()
    {
        return $this->exceptionUid;
    }

    /**
     * @param int $exceptionUid
     */
    public function setExceptionUid($exceptionUid)
    {
        $this->exceptionUid = $exceptionUid;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ImportSummary
     */
    public function getImportSummary()
    {
        return $this->importSummary;
    }

    /**
     * @param ImportSummary $importSummary
     */
    public function setImportSummary(ImportSummary $importSummary)
    {
        $this->importSummary = $importSummary;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param array $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    /**
     * @return array
     */
    public function getRowContent()
    {
        return $this->rowContent;
    }

    /**
     * @param array $rowContent
     */
    public function setRowContent($rowContent)
    {
        $this->rowContent = $rowContent;
        $this->setMd5RowContent(md5(implode('"', $rowContent)));
    }

    /**
     * @return string
     */
    public function getStringRow()
    {
        return implode('"', $this->getRowContent());
    }

    /**
     * @return string
     */
    public function getMd5RowContent()
    {
        return $this->md5RowContent;
    }

    /**
     * @param string $md5RowContent
     */
    protected function setMd5RowContent($md5RowContent)
    {
        $this->md5RowContent = $md5RowContent;
    }
}
