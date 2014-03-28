<?php

namespace RentJeeves\LandlordBundle\Accounting\FileReader;

use JMS\DiExtraBundle\Annotation as DI;
use SplFileObject;
use RuntimeException;
use RentJeeves\ComponentBundle\FileReader\CsvFileReader as Base;

/**
 * @DI\Service("import.reader.csv")
 */
class CsvFileReader extends Base
{
    public function read($filename, $offset = null, $rowCount = null)
    {
        $result = array();

        $file = $file = $this->getFile($filename);

        $header = $this->readHeader($file);
        $currentLineNumber = 0;
        $offsetMax = $offset + $rowCount;
        while ($row = $file->current()) {
            $currentLineNumber++;
            if (is_null($offset) && is_null($rowCount)) {
                $this->getLine($row, $header, $file, $result);
                continue;
            }

            if ($currentLineNumber > $offset && $currentLineNumber < $offsetMax) {
                $this->getLine($row, $header, $file, $result);
                continue;
            }

            if ($currentLineNumber > $offsetMax) {
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
}
