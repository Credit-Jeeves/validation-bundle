<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Helpers\CountryNameStandardizer;
use RentJeeves\CoreBundle\Services\AddressLookup\AddressLookupInterface;
use RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupException;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportMappingChoice;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\DataBundle\Enum\ImportPropertyStatus;
use RentJeeves\ImportBundle\Exception\ImportTransformerException;

/**
 * Service`s name "import.property.transformer.csv"
 */
class CsvTransformer implements TransformerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AddressLookupInterface
     */
    protected $lookupService;

    /**
     * @var array
     */
    protected $requiredMappingFields = [
        'street', 'city', 'zip', 'state', 'unit_id'
    ];

    /**
     * @param EntityManagerInterface $em
     * @param AddressLookupInterface $addressLookupService
     * @param LoggerInterface        $logger
     */
    public function __construct(
        EntityManagerInterface $em,
        AddressLookupInterface $addressLookupService,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->lookupService = $addressLookupService;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function transformData(array $accountingSystemData, Import $import)
    {
        $group = $import->getGroup();
        $this->logger->info(
            sprintf(
                'Started CSV transform data for Import#%d.',
                $import->getId()
            ),
            ['group' => $group]
        );

        if (empty($accountingSystemData['hashHeader'])) {
            throw new ImportTransformerException(
                sprintf('Input array should contain not empty "hashHeader".')
            );
        }

        if (null === $importMapping = $this->findImportMapping($group, $accountingSystemData['hashHeader'])) {
            $message = sprintf(
                'Group#%d doesn`t have importMapping for hash = "%s"',
                $group->getId(),
                $accountingSystemData['hashHeader']
            );
            $this->logger->warning($message, ['group' => $group]);
            throw new ImportTransformerException($message);
        }

        $importMappingRule = $this->getImportMappingRule($importMapping);
        $countryFromSettings = $group->getGroupSettings()->getCountryCode();

        foreach ($accountingSystemData['data'] as $accountingSystemRecord) {
            $street = $accountingSystemRecord[$importMappingRule['street']];
            $city = $accountingSystemRecord[$importMappingRule['city']];
            $state = $accountingSystemRecord[$importMappingRule['state']];
            $zip = $accountingSystemRecord[$importMappingRule['zip']];

            if (false === isset($importMappingRule['country']) ) {
                $country = $countryFromSettings;
            } else {
                $country = CountryNameStandardizer::standardize(
                    $accountingSystemRecord[$importMappingRule['country']]
                );
            }

            $unit = isset($importMappingRule['unit']) ? $accountingSystemRecord[$importMappingRule['unit']] : '';
            $extUnitId = isset($importMappingRule['unit_id']) ?
                $accountingSystemRecord[$importMappingRule['unit_id']] : '';

            $importProperty = new ImportProperty();
            $importProperty->setImport($import);

            try {
                $address = $this->lookupService->lookupFreeform(
                    sprintf(
                        '%s %s, %s, %s, %s',
                        $street,
                        $unit,
                        $city,
                        $state,
                        $zip
                    ),
                    $country
                );

                $importProperty->setAddressHasUnits((boolean) $address->getUnitName());
                $importProperty->setUnitName($address->getUnitName());
            } catch (AddressLookupException $e) {
                $importProperty->setProcessed(true);
                $importProperty->setStatus(ImportPropertyStatus::ERROR);
                $importProperty->setErrorMessages([
                    'Address is invalid'
                ]);
                $importProperty->setUnitName($unit);
                $importProperty->setAddressHasUnits((boolean) $unit);
            }

            $importProperty->setExternalUnitId($extUnitId);
            $importProperty->setAddress1($street);
            $importProperty->setCity($city);
            $importProperty->setState($state);
            $importProperty->setZip($zip);
            $importProperty->setCountry($country);
            $importProperty->setAllowMultipleProperties(false);

            $this->em->persist($importProperty);
        }

        $this->em->flush();
    }

    /**
     * @param ImportMappingChoice $importMappingChoice
     *
     * @throws ImportTransformerException if Mapping doesn`t contain required fields
     *
     * @return array
     */
    protected function getImportMappingRule(ImportMappingChoice $importMappingChoice)
    {
        $mappingData = $importMappingChoice->getMappingData();
        $mappingRule = array_flip($mappingData);

        $missingFields = [];
        foreach ($this->requiredMappingFields as $requiredMappingField) {
            if (false === isset($mappingRule[$requiredMappingField])) {
                $missingFields[] = $requiredMappingField;
            }
        }

        if (false === empty($missingFields)) {
            $message = sprintf(
                'ImportMapping doesn`t contain mapping for required field(s): %s',
                implode(', ', $missingFields)
            );
            $this->logger->warning($message, ['group' => $importMappingChoice->getGroup()]);
            throw new ImportTransformerException($message);
        }

        $importMappingRule = [];
        foreach ($mappingRule as $key => $value) {
            $importMappingRule[$key] = $value - 1;
        }

        return $importMappingRule;
    }

    /**
     * @param Group  $group
     * @param string $headerHash
     *
     * @return \RentJeeves\DataBundle\Entity\ImportMappingChoice
     */
    protected function findImportMapping(Group $group, $headerHash)
    {
        return $this->em->getRepository('RjDataBundle:ImportMappingChoice')->findOneBy(
            [
                'group' => $group,
                'headerHash' => $headerHash,
            ]
        );
    }
}
