<?php

namespace RentJeeves\ImportBundle\PropertyImport;

use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Enum\ImportModelType;
use RentJeeves\ImportBundle\Exception\ImportException;
use RentJeeves\ImportBundle\Exception\ImportLogicException;
use RentJeeves\ImportBundle\PropertyImport\Extractor\ExtractorFactory;
use RentJeeves\ImportBundle\PropertyImport\Loader\PropertyLoader;
use RentJeeves\ImportBundle\PropertyImport\Transformer\TransformerFactory;

/**
 * Service`s name "import.property.manager"
 */
class ImportPropertyManager
{
    /**
     * @var ExtractorFactory
     */
    protected $extractorFactory;

    /**
     * @var TransformerFactory
     */
    protected $transformerFactory;

    /**
     * @var PropertyLoader
     */
    protected $propertyLoader;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ExtractorFactory   $extractorFactory
     * @param TransformerFactory $transformerFactory
     * @param PropertyLoader     $propertyLoader
     * @param LoggerInterface    $logger
     */
    public function __construct(
        ExtractorFactory $extractorFactory,
        TransformerFactory $transformerFactory,
        PropertyLoader $propertyLoader,
        LoggerInterface $logger
    ) {
        $this->extractorFactory = $extractorFactory;
        $this->transformerFactory = $transformerFactory;
        $this->propertyLoader = $propertyLoader;
        $this->logger = $logger;
    }

    /**
     * @param Import $import
     * @param string $externalPropertyId
     *
     * @throws ImportLogicException when U use this service for Import with type != property
     */
    public function import(Import $import, $externalPropertyId)
    {
        $group = $import->getGroup();
        if ($import->getImportType() !== ImportModelType::PROPERTY) {
            $this->logger->warning(
                $message = sprintf(
                    'Invalid import type. Should be "%s" instead "%s".',
                    ImportModelType::PROPERTY,
                    $import->getImportType()
                ),
                ['group_id' => $group->getId()]
            );
            throw new ImportLogicException($message);
        }

        $this->logger->info(
            sprintf('Start import data for Import#%d and extPropertyId#%s.', $import->getId(), $externalPropertyId),
            ['group_id' => $group->getId()]
        );
        try {
            $extractor = $this->extractorFactory->getExtractor($group->getHolding()->getAccountingSystem());
            $this->logger->info(
                sprintf(
                    'ImportPropertyManager for Import#%d and extPropertyId#%s will use "%s" for extract data.',
                    $import->getId(),
                    $externalPropertyId,
                    get_class($extractor)
                ),
                ['group_id' => $group->getId()]
            );

            $extractedData = $extractor->extractData($group, $externalPropertyId);

            if (false === empty($extractedData)) {
                $transformer = $this->transformerFactory->getTransformer($group, $externalPropertyId);
                $this->logger->info(
                    sprintf(
                        'ImportPropertyManager for Import#%d and extPropertyId#%s will use "%s" for transform data.',
                        $import->getId(),
                        $externalPropertyId,
                        get_class($transformer)
                    ),
                    ['group_id' => $group->getId()]
                );
                $transformer->transformData($extractedData, $import);
                $this->propertyLoader->loadData($import, $externalPropertyId);
            }
        } catch (ImportException $e) {
            $this->logger->info(
                sprintf(
                    'Import data for Import#%d and extPropertyId#%s is finished with error : %s.',
                    $import->getId(),
                    $externalPropertyId,
                    $e->getMessage()
                ),
                ['group_id' => $group->getId()]
            );

            return;
        }

        $this->logger->info(
            sprintf(
                'Import data for Import#%d and extPropertyId#%s is finished.',
                $import->getId(),
                $externalPropertyId
            ),
            ['group_id' => $group->getId()]
        );
    }
}
