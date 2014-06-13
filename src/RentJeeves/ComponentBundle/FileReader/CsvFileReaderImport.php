<?php

namespace RentJeeves\ComponentBundle\FileReader;

use JMS\DiExtraBundle\Annotation as DI;
use SplFileObject;
use RuntimeException;
use RentJeeves\ComponentBundle\FileReader\CsvFileReader as Base;

/**
 * @DI\Service("import.reader.csv")
 */
class CsvFileReaderImport extends Base
{
    protected $linesTotal = array();

    public function read($filename, $offset = null, $rowCount = null)
    {
        $result = array();
        $offsetMax = 0;
        $file = $this->getFile($filename);

        $totalLines = $this->countLines($file);

        if (!is_null($offset) && $offset >= $totalLines) {
            return $result;
        }

        if (!is_null($offset)) {
            //Because first line is header
            $offset += 1;
            $file->seek($offset);
            $offsetMax = $offset + $rowCount;
        }

        $header = $this->readHeader($file);
        while ($row = $file->current()) {
            if (is_null($offset) && is_null($rowCount)) {
                $this->getLine($row, $header, $file, $result);
                continue;
            }
            $lineNumber = $file->key();
            if ($lineNumber < $offsetMax) {
                $this->getLine($row, $header, $file, $result);
                continue;
            }
            break;
        }

        return $result;
    }

    public function countLines($file)
    {
        if (!($file instanceof SplFileObject)) {
            $file = $this->getFile($file);
        }

        if (isset($this->linesTotal[$file->getFilename()])) {
            return $this->linesTotal[$file->getFilename()];
        }

        $this->linesTotal[$file->getFilename()] = 0;
        // force to seek to last line, won't raise error
        while ($row = $file->current()) {
            $file->next();
            $this->linesTotal[$file->getFilename()] = $file->key();
        }
        $file->rewind();
        return $this->linesTotal[$file->getFilename()];
    }

    protected function getLine($row, $header, $file, &$result)
    {
        foreach ($row as $valueIndex => $value) {
            if (!isset($header[$valueIndex])) {
                continue;
            }
            $result[$file->key()][$header[$valueIndex]] = $value;
        }
        $file->next();
    }
}
