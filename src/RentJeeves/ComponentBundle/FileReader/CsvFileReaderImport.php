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
    public function read($filename, $offset = null, $rowCount = null)
    {
        $result = array();
        $offsetMax = 0;
        $file = $this->getFile($filename);

        $totalLines = $this->countLines($file);

        if (!is_null($offset) && ++$offset >= $totalLines) { // ++offset Because first line is header
            return $result;
        }

        if (!is_null($offset)) {
            $file->seek($offset);
            $offsetMax = $offset + $rowCount;
        }

        $header = $this->fixHeaderDuplicatesValue(
            $this->readHeader($file)
        );

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

        $total = 0;
        // force to seek to last line, won't raise error
        while ($file->current()) {
            $file->next();
            $total++;
        }

        return $total;
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

    protected function fixHeaderDuplicatesValue($header)
    {
        $result = array();
        $counter = array();
        $valueUsed = array();
        foreach ($header as $key => $value) {
            if (!isset($valueUsed[$value])) {
                $result[$key] = $value;
                $counter[$value] = 1;
                $valueUsed[$value] = $key;
                continue;
            }

            if (isset($valueUsed[$value]) && $counter[$value] === 1) {
                $result[$valueUsed[$value]] = $result[$valueUsed[$value]].'_'.$counter[$value];
            }
            
            $counter[$value] += 1;
            $result[$key] = $value.'_'.$counter[$value];
        }

        return $result;
    }
}
