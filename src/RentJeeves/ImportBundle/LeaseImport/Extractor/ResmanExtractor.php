<?php

namespace RentJeeves\ImportBundle\LeaseImport\Extractor;

use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\ResManSettings;
use RentJeeves\ExternalApiBundle\Services\ResMan\ResidentDataManager as ResmanResidentDataManager;
use RentJeeves\ImportBundle\Exception\ImportExtractorException;
use RentJeeves\ImportBundle\Exception\ImportLogicException;
use RentJeeves\ImportBundle\LeaseImport\Extractor\Interfaces\ApiLeaseExtractorInterface;
use RentJeeves\ImportBundle\Traits\SetupExternalPropertyIdTrait;
use RentJeeves\ImportBundle\Traits\SetupGroupTrait;

/**
 * Service`s name "import.lease.extractor.resman" (public = false)
 */
class ResmanExtractor implements ApiLeaseExtractorInterface
{
    use SetupGroupTrait;
    use SetupExternalPropertyIdTrait;

    /**
     * @var ResmanResidentDataManager
     */
    protected $residentDataManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ResmanResidentDataManager $residentDataManager
     * @param LoggerInterface           $logger
     */
    public function __construct(ResmanResidentDataManager $residentDataManager, LoggerInterface $logger)
    {
        $this->residentDataManager = $residentDataManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function extractData()
    {
        if (null === $this->group || null === $this->externalPropertyId) {
            throw new ImportLogicException(
                'Pls configure extractor("setGroup","setExternalPropertyId") before extractData.'
            );
        }
        $this->logger->info(
            'Starting process Resman extractData.',
            ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
        );

        if (!$this->group->getIntegratedApiSettings() instanceof ResManSettings) {
            $this->logger->warning(
                $message = 'Group has incorrect settings for ResmanExtractor.',
                ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
            );

            throw new ImportExtractorException($message);
        }

        $this->residentDataManager->setSettings($this->group->getIntegratedApiSettings());

        $data = $this->residentDataManager->getResidentTransactions($this->externalPropertyId);

        if (true === empty($data)) {
            $this->logger->info(
                'Returned response is empty.',
                ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
            );
        }

        $this->logger->info(
            'Finished process extractData.',
            ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
        );

        return $data;
    }
}
