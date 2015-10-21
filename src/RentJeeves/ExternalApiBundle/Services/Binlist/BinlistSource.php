<?php

namespace RentJeeves\ExternalApiBundle\Services\Binlist;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use RentJeeves\ComponentBundle\FileReader\CsvFileReader;

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
     * Loads scv binlist data from github repo.
     * As ScvReader works with files only, we need to save content to file first, and then parse that content.
     * Returns an associative array if fieldName=>fieldValue from binlist.
     *
     * @return array
     */
    public function loadBinlistData()
    {
        try {
            $content = file_get_contents(self::SOURCE);
            $path = sprintf('%s%s%s.csv', sys_get_temp_dir(), DIRECTORY_SEPARATOR, uniqid());
            file_put_contents($path, $content);

            return $this->csvReader->read($path);
        } catch (\Exception $e) {
            $this->logger->alert(sprintf('Could not load binlist data: %s', $e->getMessage()));
            throw $e;
        }
    }
}
