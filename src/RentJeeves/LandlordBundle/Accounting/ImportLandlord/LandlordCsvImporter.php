<?php

namespace RentJeeves\LandlordBundle\Accounting\ImportLandlord;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RentJeeves\ComponentBundle\FileReader\CsvFileReader;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Partner;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\LandlordBundle\Accounting\ImportLandlord\Exception\DuplicatedUnitException;
use RentJeeves\LandlordBundle\Accounting\ImportLandlord\Exception\MappingException;
use RentJeeves\LandlordBundle\Accounting\ImportLandlord\Mapping\GroupMapper;
use RentJeeves\LandlordBundle\Accounting\ImportLandlord\Mapping\LandlordMapper;
use RentJeeves\LandlordBundle\Accounting\ImportLandlord\Mapping\UnitMapper;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator;

class LandlordCsvImporter implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var CsvFileReader
     */
    protected $csvReader;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var GroupMapper
     */
    protected $groupMapper;

    /**
     * @var LandlordMapper
     */
    protected $landlordMapper;

    /**
     * @var UnitMapper
     */
    protected $unitMapper;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var array
     */
    protected $mappingErrors;

    /**
     * @param CsvFileReader $csvFileReader
     * @param EntityManagerInterface $em
     * @param GroupMapper $groupMapper
     * @param LandlordMapper $landlordMapper
     * @param UnitMapper $unitMapper
     * @param Validator $validator
     */
    public function __construct(
        CsvFileReader $csvFileReader,
        EntityManagerInterface $em,
        GroupMapper $groupMapper,
        LandlordMapper $landlordMapper,
        UnitMapper $unitMapper,
        Validator $validator
    ) {
        $this->em = $em;
        $this->csvReader = $csvFileReader;
        $this->csvReader->setConvertHeaderToLowercase(true);
        $this->groupMapper = $groupMapper;
        $this->landlordMapper = $landlordMapper;
        $this->unitMapper = $unitMapper;
        $this->validator = $validator;

        $this->mappingErrors = [];
    }

    /**
     * Import Landlord
     * and
     * related entities from file
     *
     * @param string $pathToFile
     * @param Partner $partner
     *
     * @throws \Exception
     */
    public function importPartnerLandlords($pathToFile, Partner $partner)
    {
        try {
            foreach ($this->csvReader->read($pathToFile) as $row) {
                $this->importRow($row, $partner);
            }
        } catch (\Exception $e) {
            $this->logger->error(sprintf('[Landlord CSV import]: %s', $e->getMessage()));

            throw $e;
        }
    }

    /**
     * @param array $row
     * @param Partner $partner
     */
    protected function importRow(array $row, Partner $partner)
    {
        try {
            $group = $this->groupMapper->map($row);
            $landlord = $this->landlordMapper->map($row, $group);
            $unit = $this->unitMapper->map($row, $group);

            $landlord->setPartner($partner);

            $errors = $this->validateEntities($group, $landlord, $unit);

            if (false === empty($errors)) {
                $this->addErrorForRow(implode(PHP_EOL, array_values($errors)), $row);
            } else {
                $this->em->persist($group);
                $this->em->persist($landlord);
                $this->em->persist($unit);
                $this->em->flush();
            }
        } catch (MappingException $e) {
            $this->addErrorForRow($e->getMessage(), $row);
        } catch (DuplicatedUnitException $e) {
            $this->addErrorForRow($e->getMessage(), $row);
            $this->logger->debug(sprintf('[Landlord CSV import]: %s', $e->getMessage()));
        }
    }

    /**
     * @param string $errorMessage
     * @param array $row
     */
    protected function addErrorForRow($errorMessage, array $row)
    {
        $rowToString = implode(',', array_values($row));
        foreach ($this->mappingErrors as $key => $errorsForRow) {
            if ($errorsForRow['row'] === $rowToString) {
                $this->mappingErrors[$key]['messages'][] = $errorMessage;

                return;
            }
        }

        $this->mappingErrors[] = [
            'messages' => [$errorMessage],
            'row' => implode(',', array_values($row))
        ];
    }

    /**
     * @param Group $group
     * @param Landlord $landlord
     * @param Unit $unit
     *
     * @return array
     */
    protected function validateEntities(Group $group, Landlord $landlord, Unit $unit)
    {
        $groupErrors = $this->validator->validate($group, ['landlordImport']);
        $landlordErrors = $this->validator->validate($landlord, ['landlordImport']);
        $unitErrors = $this->validator->validate($unit, ['landlordImport']);

        $errors = [];
        /** @var ConstraintViolation $constraint */
        foreach ($groupErrors as $constraint) {
            $errors[] = sprintf('[Group] %s : %s', $constraint->getPropertyPath(), $constraint->getMessage());
        }
        foreach ($landlordErrors as $constraint) {
            $errors[] = sprintf('[Landlord] %s : %s', $constraint->getPropertyPath(), $constraint->getMessage());
        }
        foreach ($unitErrors as $constraint) {
            $errors[] = sprintf('[Unit] %s : %s', $constraint->getPropertyPath(), $constraint->getMessage());
        }

        return $errors;
    }

    /**
     * @return array
     */
    public function getMappingErrors()
    {
        return $this->mappingErrors;
    }
}
