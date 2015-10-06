<?php

namespace RentJeeves\ExternalApiBundle\Services\Binlist;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use RentJeeves\ComponentBundle\FileReader\CsvFileReader;
use JMS\Serializer\Serializer;

class BinlistSource
{
    const SOURCE = 'https://raw.githubusercontent.com/binlist/binlist-data/master/iin-user-contributions.csv';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CsvFileReader
     */
    protected $csvReader;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @param LoggerInterface $logger
     * @param CsvFileReader $csvReader
     * @param Serializer $serializer
     */
    public function __construct(LoggerInterface $logger, CsvFileReader $csvReader, Serializer $serializer)
    {
        $this->csvReader = $csvReader;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getFullBinlistCsv()
    {
        try {
            $content = file_get_contents(self::SOURCE);
        } catch (\Exception $e) {
            $this->logger->alert(sprintf('Could not get data(binlist): %s', $e->getMessage()));
            throw $e;
        }

        return $content;
    }

    /**
     * @param string $pathToCsv
     * @return ArrayCollection
     */
    protected function mapCsvToCollection($pathToCsv)
    {
        $this->logger->debug('Start map csv string to collection of objects');
        $result = $this->csvReader->read($pathToCsv);
        $result = $this->serializer->deserialize(
            json_encode($result),
            'ArrayCollection<RentJeeves\DataBundle\Entity\DebitCardBinlist>',
            'json'
        );

        return $result;
    }

    /**
     * @param string $data
     * @return string
     */
    protected function writeResponseToFile($data)
    {
        $path = sprintf(
            '%s%s%s.csv',
            sys_get_temp_dir(),
            DIRECTORY_SEPARATOR,
            uniqid()
        );

        file_put_contents($path, $data);
        $this->logger->debug(sprintf('Path to csv: %s', $path));

        return $path;
    }

    /**
     * @return ArrayCollection
     */
    public function getBinListCollection()
    {
        $pathToCsv = $this->writeResponseToFile($this->getFullBinlistCsv());

        return $this->mapCsvToCollection($pathToCsv);
    }
}
