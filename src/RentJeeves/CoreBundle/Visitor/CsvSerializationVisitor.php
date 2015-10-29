<?php

namespace RentJeeves\CoreBundle\Visitor;

use JMS\Serializer\AbstractVisitor;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\Serializer\VisitorInterface;

/**
 * @Service("jms_serializer.csv_serialization_visitor")
 * @Tag("jms_serializer.serialization_visitor", attributes = {"format" = "csv"})
 */
class CsvSerializationVisitor extends AbstractVisitor implements VisitorInterface
{

    protected $navigator;

    /**
     * @var string
     */
    protected $delimiter;

    /**
     * @var string
     */
    protected $enclosure;

    protected $fp;

    // $eol: probably one of "\r\n", "\n", or for super old macs ^-^ : "\r"
    protected $eol = "\n";

    protected $currentLine = [];

    private $headerFlag = false;

    /**
     * @param string $delimiter
     * @param string $enclosure
     */
    public function __construct($delimiter = ',', $enclosure = '"')
    {
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;

        $this->initOutput();
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitNull($data, array $type, Context $context)
    {
        return "";
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitString($data, array $type, Context $context)
    {
        return (string) $data;
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitBoolean($data, array $type, Context $context)
    {
        return ($data) ? 'true' : 'false';
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitDouble($data, array $type, Context $context)
    {
        return floatval($data);
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitInteger($data, array $type, Context $context)
    {
        return (int) $data;
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitArray($data, array $type, Context $context)
    {
        foreach ($data as $k => $v) {
            if (null === $v && (!is_string($k) || !$context->shouldSerializeNull())) {
                continue;
            }

            $this->navigator->accept($v, null, $context);
        }
    }

    /**
     * Called before the properties of the object are being visited.
     * Sets headerFlag to true in order to not show the header if corresponding parameter use_header is passed.
     *
     * @param ClassMetadata $metadata
     * @param mixed $data
     * @param array $type
     *
     * @param \JMS\Serializer\Context $context
     * @return void
     */
    public function startVisitingObject(ClassMetadata $metadata, $data, array $type, Context $context)
    {
        $useHeader = $context->attributes->get('use_header');
        if ($useHeader instanceof \PhpOption\Some && $useHeader->get() === false) {
            $this->headerFlag = true;
        }

        $eol = $context->attributes->get('eol');
        if ($eol instanceof \PhpOption\Some && $eol = $eol->get()) {
            $this->eol = $eol;
        }

    }

    /**
     * @param PropertyMetadata $metadata
     * @param mixed $data
     *
     * @return void
     */
    public function visitProperty(PropertyMetadata $metadata, $data, Context $context)
    {
        $name  = $metadata->serializedName ?: $metadata->name;
        $value = $metadata->getValue($data);

        $this->currentLine[$name] = $this->navigator->accept($value, $metadata->type, $context);
    }

    /**
     * Called after all properties of the object have been visited.
     *
     * @param ClassMetadata $metadata
     * @param mixed $data
     * @param array $type
     * @param \JMS\Serializer\Context $context
     *
     * @return mixed
     */
    public function endVisitingObject(ClassMetadata $metadata, $data, array $type, Context $context)
    {
        if ($this->headerFlag === false) {
            $this->fputcsvEol(array_keys($this->currentLine));
            $this->headerFlag = true;
        }

        $this->fputcsvEol($this->currentLine);
        $this->currentLine = array();
    }

    /**
     * Called before serialization/deserialization starts.
     *
     * @param GraphNavigator $navigator
     *
     * @return self
     */
    public function setNavigator(GraphNavigator $navigator)
    {
        $this->navigator = $navigator;

        return $this;
    }

    /**
     * @deprecated use Context::getNavigator/Context::accept instead
     * @return GraphNavigator
     */
    public function getNavigator()
    {
        return $this->navigator;
    }

    /**
     * @return string
     */
    public function getResult()
    {
        rewind($this->fp);
        $data = fread($this->fp, 1048576);
        fclose($this->fp);
        $this->initOutput();

        return $data;
    }

    protected function initOutput()
    {
        $this->fp = fopen('php://temp', 'r+');
        $this->headerFlag = false;
    }

    protected function fputcsvEol($array)
    {
        fputcsv($this->fp, $array, $this->delimiter, $this->enclosure);
        if ("\n" != $this->eol && 0 === fseek($this->fp, -1, SEEK_CUR)) {
            fwrite($this->fp, $this->eol);
        }
    }
}
