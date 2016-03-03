<?php

namespace RentJeeves\ImportBundle\PropertyImport;

use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Enum\ImportModelType;
use RentJeeves\ImportBundle\Exception\ImportException;
use RentJeeves\ImportBundle\Exception\ImportLogicException;
use RentJeeves\ImportBundle\PropertyImport\Extractor\ExtractorBuilder;
use RentJeeves\ImportBundle\PropertyImport\Loader\LoaderFactory;
use RentJeeves\ImportBundle\PropertyImport\Transformer\TransformerFactory;

/**
 * Service`s name "import.property.manager"
 */
class ImportPropertyManager
{
    /**
     * @var ExtractorBuilder
     */
    protected $extractorBuilder;

    /**
     * @var TransformerFactory
     */
    protected $transformerFactory;

    /**
     * @var LoaderFactory
     */
    protected $loaderFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ExtractorBuilder   $extractorBuilder
     * @param TransformerFactory $transformerFactory
     * @param LoaderFactory      $loaderFactory
     * @param LoggerInterface    $logger
     */
    public function __construct(
        ExtractorBuilder $extractorBuilder,
        TransformerFactory $transformerFactory,
        LoaderFactory $loaderFactory,
        LoggerInterface $logger
    ) {
        $this->extractorBuilder = $extractorBuilder;
        $this->transformerFactory = $transformerFactory;
        $this->loaderFactory = $loaderFactory;
        $this->logger = $logger;
    }

    /**
     * @param Import $import
     * @param string $additionalParameter pathForFile or extPropertyId
     *
     * @throws ImportLogicException when U use this service for Import with type != property
     */
    public function import(Import $import, $additionalParameter)
    {
        $group = $import->getGroup();
        if ($import->getImportType() !== ImportModelType::PROPERTY) {
            $this->logger->warning(
                $message = sprintf(
                    'Invalid import type. Should be "%s" instead "%s".',
                    ImportModelType::PROPERTY,
                    $import->getImportType()
                ),
                ['group' => $group, 'additional_parameter' => $additionalParameter]
            );
            throw new ImportLogicException($message);
        }

        $this->logger->info(
            sprintf('Start import data for Import#%d.', $import->getId()),
            ['group' => $group, 'additional_parameter' => $additionalParameter]
        );
        try {
            $extractor = $this->extractorBuilder
                ->setGroup($group)
                ->setAdditionalParameter($additionalParameter)
                ->build();
            $this->logger->info(
                sprintf(
                    'ImportPropertyManager for Import#%d will use "%s" for extract data.',
                    $import->getId(),
                    get_class($extractor)
                ),
                ['group' => $group, 'additional_parameter' => $additionalParameter]
            );
            $extractedData = $extractor->extractData();
            if (false === empty($extractedData)) {
                $transformer = $this->transformerFactory->getTransformer($group, $additionalParameter);
                $this->logger->info(
                    sprintf(
                        'ImportPropertyManager for Import#%d will use "%s" for transform data.',
                        $import->getId(),
                        get_class($transformer)
                    ),
                    ['group' => $group, 'additional_parameter' => $additionalParameter]
                );
                $transformer->transformData($extractedData, $import);

                $loader = $this->loaderFactory->getLoader($group);
                $this->logger->info(
                    sprintf(
                        'ImportPropertyManager for Import#%d will use "%s" for load data.',
                        $import->getId(),
                        get_class($loader)
                    ),
                    ['group' => $group, 'additional_parameter' => $additionalParameter]
                );
                $loader->loadData($import, $additionalParameter);
            }
        } catch (ImportException $e) {
            $this->logger->info(
                sprintf(
                    'Import data for Import#%d is finished with error : %s.',
                    $import->getId(),
                    $e->getMessage()
                ),
                ['group' => $group, 'additional_parameter' => $additionalParameter]
            );

            return;
        }

        $this->logger->info(
            sprintf(
                'Import data for Import#%d is finished.',
                $import->getId()
            ),
            ['group' => $group, 'additional_parameter' => $additionalParameter]
        );
    }
}
