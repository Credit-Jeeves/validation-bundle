<?php

namespace RentJeeves\ExternalApiBundle\Services\Binlist;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use RentJeeves\ComponentBundle\FileReader\CsvFileReader;
use RentJeeves\DataBundle\Entity\DebitCardBinlist;

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
     * @param LoggerInterface $logger
     * @param CsvFileReader $csvReader
     */
    public function __construct(LoggerInterface $logger, CsvFileReader $csvReader)
    {
        $this->csvReader = $csvReader;
        $this->logger = $logger;
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
        $collection = new ArrayCollection();
        foreach ($result as $values) {
            $debitCardBinlist = new DebitCardBinlist();
            $debitCardBinlist->setIin($values['iin']);
            $debitCardBinlist->setCardBrand($values['card_brand']);
            $debitCardBinlist->setCardSubBrand($values['card_sub_brand']);
            $debitCardBinlist->setCardType($values['card_type']);
            $debitCardBinlist->setCardCategory($values['card_category']);
            $debitCardBinlist->setCountryCode($values['country_code']);
            $debitCardBinlist->setBankName($values['bank_name']);
            $debitCardBinlist->setBankUrl($values['bank_url']);
            $debitCardBinlist->setBankPhone($values['bank_phone']);
            $debitCardBinlist->setBankCity($values['bank_city']);

            $collection->add($debitCardBinlist);
        }
        $this->logger->debug(sprintf('Return collection of element in %s', $collection->count()));

        return $collection;
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
