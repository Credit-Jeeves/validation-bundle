<?php

namespace RentJeeves\ComponentBundle\FileReader;

use JMS\DiExtraBundle\Annotation as DI;
use SplFileObject;
use RuntimeException;

/**
 * @DI\Service("reader.csv")
 */
class CsvFileReader
{
    protected $delimiter = ',';
    protected $enclosure = '"';
    protected $escape    = '\\';
    protected $useHeader = true;

    /**
     * @param string $delimiter
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * @param string $enclosure
     */
    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;
    }

    /**
     * @return string
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * @param string $escape
     */
    public function setEscape($escape)
    {
        $this->escape = $escape;
    }

    /**
     * @return string
     */
    public function getEscape()
    {
        return $this->escape;
    }

    /**
     * @param boolean $useHeader
     */
    public function setUseHeader($useHeader)
    {
        $this->useHeader = $useHeader;
    }

    /**
     * @return boolean
     */
    public function getUseHeader()
    {
        return $this->useHeader;
    }

    public function read($filename, $offset = null, $rowCount = null)
    {
        if (!file_exists($filename)) {
            throw new RuntimeException(sprintf('File "%s" not found.', $filename));
        }

        $result = array();

        $file = new SplFileObject($filename, 'rb');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl($this->delimiter, $this->enclosure, $this->escape);

        $header = $this->readHeader($file);
        $currentNumberOfLine = 0;
        $offsetMax = $offset+$rowCount;
        while ($row = $file->current()) {
            $currentNumberOfLine++;
            if (is_null($offset) && is_null($rowCount)) {
                $this->getLine($row, $header, $file, $result);
                continue;
            }

            if ($currentNumberOfLine > $offset && $currentNumberOfLine < $offsetMax) {
                $this->getLine($row, $header, $file, $result);
                continue;
            }

            if ($currentNumberOfLine > $offsetMax) {
                break;
            }

            $file->next();
        }

        return $result;
    }

    protected function getLine($row, $header, $file, &$result)
    {
        foreach ($row as $valueIndex => $value) {
            $result[$file->key()][$header[$valueIndex]] = $value;
        }
        $file->next();
    }

    protected function readHeader(SplFileObject $file)
    {
        if ($this->useHeader) {
            $currentRowIndex = $file->key();
            $file->rewind();
            $header = $file->current();
            $targetRowIndex = $currentRowIndex > 0 ? $currentRowIndex : 1;
            $file->seek($targetRowIndex);
            return $header;
        }

        return range(0, count($file->current()));
    }
}
