<?php

namespace RentJeeves\ImportBundle\PropertyImport\Loader;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Services\PropertyManager;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ImportPropertyStatus;
use RentJeeves\ImportBundle\Exception\ImportException;
use RentJeeves\ImportBundle\Exception\ImportInvalidArgumentException;
use Symfony\Component\Validator\Validator;

abstract class AbstractLoader implements PropertyLoaderInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var PropertyManager
     */
    protected $propertyManager;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param EntityManager   $em
     * @param PropertyManager $propertyManager
     * @param Validator       $validator
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManager $em,
        PropertyManager $propertyManager,
        Validator $validator,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->propertyManager = $propertyManager;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function loadData(Import $import, $additionalParameter = null)
    {
        $this->preCheckData($import, $additionalParameter);

        $this->logger->info(
            sprintf(
                'Starting process load property from Import#%d.',
                $import->getId()
            ),
            ['group' => $import->getGroup(), 'additional_parameter' => $additionalParameter]
        );

        $iterableResult = $this->getImportProperties($import, $additionalParameter);

        /** @var ImportProperty $importProperty */
        while ((list($importProperty) = $iterableResult->next()) !== false) {
            try {
                $this->processImportProperty($importProperty, $additionalParameter);
                $this->em->flush($importProperty);
                $this->em->clear();
            } catch (\Exception $e) {
                // if any error occurs, set record as failed and keep going!
                $this->setImportRecordFailed($importProperty, $e);
            }
        }

        $this->logger->info(
            sprintf(
                'Finished process load property from Import#%d.',
                $import->getId()
            ),
            ['group' => $import->getGroup(), 'additional_parameter' => $additionalParameter]
        );
    }

    /**
     * @param Import $import
     * @param null|string $additionalParameter
     * @throws ImportInvalidArgumentException
     */
    abstract protected function preCheckData(Import $import, $additionalParameter);

    /**
     * @param Import $import
     * @param null|string $additionalParameter
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    protected function getImportProperties(Import $import, $additionalParameter)
    {
        return $this->em
            ->getRepository('RjDataBundle:ImportProperty')
            ->getNotProcessedImportProperties($import, $additionalParameter);
    }

    /**
     * @param ImportProperty $importProperty
     * @param null $additionalParameter
     */
    protected function processImportProperty(ImportProperty $importProperty, $additionalParameter = null)
    {
        $this->logger->debug(
            sprintf('Start processing ImportProperty#%d', $importProperty->getId()),
            [
                'group' => $importProperty->getImport()->getGroup(),
                'additional_parameter' => $additionalParameter
            ]
        );
        $group = $importProperty->getImport()->getGroup();
        try {
            $property = $this->processProperty($importProperty);

            $unit = $this->processUnit($property, $importProperty);

            if (!$property->getId()) {
                $importProperty->setStatus(ImportPropertyStatus::NEW_PROPERTY_AND_UNIT);
            } elseif (!$unit->getId()) {
                $importProperty->setStatus(ImportPropertyStatus::NEW_UNIT);
            } else {
                $importProperty->setStatus(ImportPropertyStatus::MATCH);
            }

            $this->saveData($importProperty, $property);
        } catch (ImportException $e) {
            $this->logger->error(
                sprintf('%s on %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()),
                [
                    'group' => $group,
                    'additional_parameter' => $additionalParameter
                ]
            );
            $this->setImportRecordFailed($importProperty, $e);
        }

        $this->logger->debug(
            sprintf(
                'Processed ImportProperty #%d with result "%s"',
                $importProperty->getId(),
                $importProperty->getStatus()
            ),
            [
                'group' => $group,
                'additional_parameter' => $additionalParameter
            ]
        );
        $importProperty->setProcessed(true);
    }

    /**
     * @param ImportProperty $importProperty
     * @return Property
     */
    abstract protected function processProperty(ImportProperty $importProperty);

    /**
     * @param Property $property
     * @param ImportProperty $importProperty
     * @return Unit
     */
    abstract protected function processUnit(Property $property, ImportProperty $importProperty);

    /**
     * Method need b/c on mapped we save property and propertyMapping but on unmapped save just property only.
     *
     * @param ImportProperty $importProperty
     * @param Property $property
     */
    abstract protected function saveData(ImportProperty $importProperty, Property $property);

    /**
     * @param ImportProperty $importProperty
     * @param \Exception $e
     */
    protected function setImportRecordFailed(ImportProperty $importProperty, \Exception $e)
    {
        $this->logger->error($e->getMessage());
        $importProperty->setStatus(ImportPropertyStatus::ERROR);
        $importProperty->setErrorMessages([
            $e->getMessage()
        ]);
    }
}
