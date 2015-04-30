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
    protected $delimiter = ",";
    protected $enclosure = "\"";
    protected $escape    = "\\";
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

    public function read($filename)
    {
        $result = array();
        $file = $this->getFile($filename);
        $header = $this->readHeader($file);

        while ($row = $file->current()) {
            foreach ($row as $valueIndex => $value) {
                $result[$file->key()][$header[$valueIndex]] = $value;
            }
            $file->next();
        }

        return $result;
    }

    protected function getFile($filename)
    {
        if (!file_exists($filename) || !is_file($filename)) {
            throw new RuntimeException(sprintf('File "%s" not found.', $filename));
        }

        ini_set('auto_detect_line_endings', true);

        $file = new SplFileObject($filename, 'rb');
        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::SKIP_EMPTY |
            SplFileObject::DROP_NEW_LINE |
            SplFileObject::READ_AHEAD
        );
        $file->setCsvControl($this->delimiter, $this->enclosure, $this->escape);
        return $file;
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
