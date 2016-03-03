<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportProperty;
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
     * @var array
     */
    protected $arrayCache = [];

    /**
     * @param EntityManagerInterface $em
     * @param LoggerInterface        $logger
     */
    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
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
                sprintf('Input array should contains not empty "hashHeader".')
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
//        a:15:{i:1;s:11:"resident_id";i:2;s:11:"tenant_name";i:3;s:4:"rent";i:4;s:7:"balance";i:5;s:7:"unit_id";i:6;s:6:"street";i:8;s:4:"unit";i:9;s:4:"city";i:10;s:5:"state";i:11;s:3:"zip";i:13;s:7:"move_in";i:14;s:9:"lease_end";i:15;s:8:"move_out";i:16;s:14:"month_to_month";i:17;s:5:"email";}
//        $importMappingRule  = $importMapping->getMappingData();
        $importMappingRule  =  array (
//            1 => 'resident_id',
//            2 => 'tenant_name',
//            3 => 'rent',
//            4 => 'balance',
            5 => 'unit_id',
            8 => 'unit',

            6 => 'street',
            9 => 'city',
            10 => 'state',
            11 => 'zip',
//            13 => 'move_in',
//            14 => 'lease_end',
//            15 => 'move_out',
//            16 => 'month_to_month',
//            17 => 'email',
        );
        foreach ($accountingSystemData['data'] as $accountingSystemRecord) {
            $importProperty = new ImportProperty();
            $importProperty->setImport($import);
            $importProperty->setExternalPropertyId($this->getExternalPropertyId($accountingSystemRecord));
            $importProperty->setExternalBuildingId($this->getExternalBuildingId($accountingSystemRecord));
            //PLS check
            $importProperty->setAddressHasUnits($this->getAddressHasUnits($accountingSystemRecord));
            //PLS check
            $importProperty->setPropertyHasBuildings($this->getPropertyHasBuildings($accountingSystemRecord));
            $importProperty->setUnitName($this->getUnitName($accountingSystemRecord));
            $importProperty->setExternalUnitId($this->getExternalUnitId($accountingSystemRecord));
            $importProperty->setAddress1($this->getAddress1($accountingSystemRecord));
            $importProperty->setCity($this->getCity($accountingSystemRecord));
            $importProperty->setState($this->getState($accountingSystemRecord));
            $importProperty->setZip($this->getZip($accountingSystemRecord));
            $importProperty->setAllowMultipleProperties($this->getAllowMultipleProperties($accountingSystemRecord));

            $this->em->persist($importProperty);
        }
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
