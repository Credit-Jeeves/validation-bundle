<?php

namespace RentJeeves\ExternalApiBundle\Services\Binlist;

use Doctrine\Common\Collections\ArrayCollection;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\ComponentBundle\FileReader\CsvFileReader;
use RentJeeves\DataBundle\Entity\DebitCardBinlist;

class BinlistSource
{
    const SOURCE = 'https://raw.githubusercontent.com/binlist/binlist-data/master/iin-user-contributions.csv';

    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CsvFileReader
     */
    protected $csvReader;

    public function __construct(LoggerInterface $logger, CsvFileReader $csvReader)
    {
        $this->client = new GuzzleClient('', ['redirect.disable' => true]);
        $this->csvReader = $csvReader;
        $this->logger = $logger;
    }

    /**
     * @return \Guzzle\Http\EntityBodyInterface|string
     * @throws CurlException
     */
    protected function getFullBinlistCsv()
    {
        $guzzleRequest = $this->client->createRequest(RequestInterface::GET, self::SOURCE);
        $guzzleResponse = $guzzleRequest->send();
        if (200 != $guzzleResponse->getStatusCode()) {
            $message = sprintf('HTTP code not %d but %d', 200, $guzzleResponse->getStatusCode());
            $this->logger->alert($message);
            throw new CurlException($message);
        }
        $this->logger->debug('Got success response from github with data');
        $responseString = $guzzleResponse->getBody(true);

        return $responseString;
    }

    /**
     * @param string $pathToCsv
     * @return ArrayCollection
     */
    protected function mapCsvToObject($pathToCsv)
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
        $this->logger->debug(sprintf('Return collection %s', $collection->count()));

        return $collection;
    }

    /**
     * @param string $data
     * @return string
     */
    protected function writeResonseToFile($data)
    {
        $path = sprintf(
            '%s%s%s%s',
            sys_get_temp_dir(),
            DIRECTORY_SEPARATOR,
            uniqid(),
            '.csv'
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
        $pathToCsv = $this->writeResonseToFile($this->getFullBinlistCsv());

        return $this->mapCsvToObject($pathToCsv);
    }
}
