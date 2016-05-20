<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\EntityManager;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\NonUniqueResultException;
use RentJeeves\CoreBundle\Services\PhoneNumberFormatter;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant as EntityTenant;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;
use RentJeeves\LandlordBundle\Model\Import;

/**
 * @TODO move static var about tenantStatus to constant when we going to refactoring
 * @property Import currentImportModel
 */
trait Tenant
{
    /**
     * @var string
     */
    public static $tenantStatusCurrent = 'c';

    /**
     * @var string
     */
    public static $tenantStatusPast = 'p';

    /**
     * @var string
     */
    public static $tenantStatusFuture = 'f';

    /**
     * @var array
     */
    protected $userResidents = [];

    /**
     * @var array
     */
    protected $userEmails = [];

    /**
     * @return bool
     */
    public function isUsedEmail()
    {
        $email = $this->currentImportModel->getTenant()->getEmail();

        return isset($this->userEmails[$email]) && $this->userEmails[$email] > 1;
    }

    /**
     * @param array $row
     */
    protected function setTenant(array $row)
    {
        $useResidentMapping = $this->isSupportResidentId();
        if ($useResidentMapping) {
            $residentId = $row[Mapping::KEY_RESIDENT_ID];
        } else {
            $residentId = null;
        }

        $email = $row[Mapping::KEY_EMAIL];
        $this->checkTenantStatus($row);
        $this->logger->debug(
            sprintf('Looking up resident by external resident id: %s or email: %s', $residentId, $email)
        );
        try {
            /** @var  EntityTenant $tenant */
            $tenant = $this->em->getRepository('RjDataBundle:Tenant')->getTenantForImportWithResident(
                $email,
                $residentId,
                $this->user->getHolding()->getId()
            );

            if (empty($tenant)) {
                //Tenant not found need look up tenant by lease ID and unit ID
                $names = $this->parseNamesFromRow($row);
                $firstName = $names[Mapping::FIRST_NAME_TENANT];
                $lastName = $names[Mapping::LAST_NAME_TENANT];
                $leaseId = (isset($row[Mapping::KEY_EXTERNAL_LEASE_ID])) ? $row[Mapping::KEY_EXTERNAL_LEASE_ID] : null;
                $unitId = (isset($row[Mapping::KEY_UNIT_ID])) ? $row[Mapping::KEY_UNIT_ID] : null;
                $this->logger->debug(
                    sprintf(
                        'Looking up resident by name %s %s and external lease id: %s or unit id: %s',
                        $firstName,
                        $lastName,
                        $leaseId,
                        $unitId
                    )
                );
                /** @var  EntityTenant $tenant */
                $tenant = $this->em->getRepository('RjDataBundle:Tenant')->getTenantByNameAndLeaseIdOrUnitId(
                    $firstName,
                    $lastName,
                    $leaseId,
                    $unitId
                );

                if (empty($tenant)) {
                    $this->logger->debug('Tenant not found by name');
                } else {
                    $this->logger->debug('Tenant found by name');
                }
            }
        } catch (NonUniqueResultException $e) {
            $this->logger->error('Caught NonUniqueResultException: ' . $e->getMessage());
            $this->currentImportModel->setTenant($this->createTenant($row));
            $errors = $this->currentImportModel->getErrors();
            $this->setUnrecoverableError(
                $this->currentImportModel->getNumber(),
                'import_contract_residentMapping_residentId',
                $this->translator->trans('import.error.none_unique_result'),
                $errors
            );
            $this->currentImportModel->setErrors($errors);

            return;
        }

        if (!empty($tenant)) {
            /** @var $residentMapping ResidentMapping */
            $residentMapping = $tenant->getResidentsMapping()->first();
            if ($useResidentMapping && $residentMapping && $residentMapping->getResidentId() !== $residentId) {
                $this->logger->warn(
                    sprintf(
                        'Imported resident id: %s doesn\'t match DB %s',
                        $residentId,
                        $residentMapping->getResidentId()
                    )
                );
                $tenant = $this->createTenant($row);
                $this->currentImportModel->setTenant($tenant);
                $errors = $this->currentImportModel->getErrors();
                $this->setUnrecoverableError(
                    $this->currentImportModel->getNumber(),
                    'import_contract_residentMapping_residentId',
                    $this->translator->trans(
                        'error.residentId.already_use',
                        [
                            '%email%'   => $residentMapping->getTenant()->getEmail(),
                            '%support_email%' => $this->supportEmail
                        ]
                    ),
                    $errors
                );
                $this->currentImportModel->setErrors($errors);

                return;
            }

            $this->logger->debug(sprintf('Tenant found: %s', $tenant->getFullName()));
            $this->currentImportModel->setTenant($tenant);
            $this->fillUsersEmailAndResident($tenant, $row);

            return;
        }

        $this->logger->debug('Tenant not found. Create new record.');
        $this->currentImportModel->setTenant($tenant = $this->createTenant($row));
        $this->fillUsersEmailAndResident($tenant, $row);
    }

    /**
     * @param $row
     * @return EntityTenant
     */
    protected function createTenant($row)
    {
        $tenant = new EntityTenant();

        $names = $this->parseNamesFromRow($row);
        $tenant->setFirstName($names[Mapping::FIRST_NAME_TENANT]);
        $tenant->setLastName($names[Mapping::LAST_NAME_TENANT]);

        if (!empty($row[Mapping::KEY_USER_PHONE])) {
            $this->setUserPhone($tenant, $row[Mapping::KEY_USER_PHONE]);
        }

        $tenant->setEmail($row[Mapping::KEY_EMAIL]);
        $tenant->setEmailCanonical($row[Mapping::KEY_EMAIL]);
        $tenant->setPassword(md5(md5(1)));
        $tenant->setCulture($this->locale);

        return $tenant;
    }

    protected function parseNamesFromRow($row)
    {
        if (!isset($row[Mapping::FIRST_NAME_TENANT]) && !isset($row[Mapping::LAST_NAME_TENANT])) {
            $names = Mapping::parseName($row[Mapping::KEY_TENANT_NAME]);
        } else {
            $names = [
                Mapping::LAST_NAME_TENANT => trim($row[Mapping::LAST_NAME_TENANT]),
                Mapping::FIRST_NAME_TENANT => trim($row[Mapping::FIRST_NAME_TENANT]),
            ];
        }

        return $names;
    }

    /**
     * @param EntityTenant $tenant
     * @param $phone
     */
    protected function setUserPhone(EntityTenant $tenant, $phone)
    {
        if ($tenant->getPhone()) {
            return;
        }

        $phone = PhoneNumberFormatter::formatToDigitsOnly($phone);
        $tenant->setPhone($phone);
        $errors = $this->validator->validate($tenant, ['import_phone']);

        if (count($errors) > 0) {
            $tenant->setPhone(null);
        }
    }

    /**
     * @param EntityTenant $tenant
     * @param $row
     */
    protected function fillUsersEmailAndResident(EntityTenant $tenant, $row)
    {
        if ($this->mapping->isSkipped($row)) {
            return;
        }

        if (!$this->isSupportResidentId()) {
            return;
        }

        $email = $tenant->getEmail();
        $residentId = $row[Mapping::KEY_RESIDENT_ID];

        if (empty($email)) {
            return;
        }

        if (!isset($this->userEmails[$email])) {
            $this->userEmails[$email] = 1;
        }

        if (!isset($this->userResidents[$email])) {
            $this->userResidents[$email] = $residentId;
        }

        $countResidents = array_count_values($this->userResidents);

        if ($this->userResidents[$email] !== $residentId || $countResidents[$residentId] > 1) {
            $this->userEmails[$email]++;
        }
    }

    /**
     * @link https://credit.atlassian.net/browse/RT-1468
     * @param array $row
     */
    protected function checkTenantStatus(array $row)
    {
        if (!isset($row[Mapping::KEY_TENANT_STATUS])) {
            return;
        }
        $tenantStatus = trim(strtolower($row[Mapping::KEY_TENANT_STATUS]));
        /**
         * Rule #1
         * If Tenant Status is "P", then finish the contract. (treat the same as if "move-out" is set)
         * If no move_out date available, then set it to today's date.
         */
        if ($tenantStatus === self::$tenantStatusPast) {
            $moveOutDate = $row[Mapping::KEY_MOVE_OUT];
            $moveOutDate = (!empty($moveOutDate)) ? $this->getDateByField($moveOutDate) : new \DateTime();
            $this->currentImportModel->setMoveOut($moveOutDate);

            return;
        }

        /**
         * Rule #2
         * If Tenant Status is "C", then do not finish, or skip contract.
         * Only finish if Move Out field is populated.
         */
        if ($tenantStatus === self::$tenantStatusCurrent) {
            return;
        }
        /** @var Group $group */
        $group = $this->getGroup($row);
        $isAllowedCreateFutureContract = false;
        if ($group && $holding = $group->getHolding()) {
            $isAllowedCreateFutureContract = $holding->isAllowedFutureContract();
        }
        /**
         * Rule/Config-Option #3
         * If Tenant Status is "F", add property
         * Set rule to
         * a) Add property/property_group_mapping/unit (if not exist), or
         * b) Add property/property_group_mapping/unit (if not exist) and add contract if PM says Yes.
         * Config option at holding level: "Add future contract"
         */
        if ($isAllowedCreateFutureContract && $tenantStatus === self::$tenantStatusFuture) {
            return;
        }

        $this->currentImportModel->setIsSkipped(true);
        $this->currentImportModel->setSkippedMessage(
            $this->translator->trans('error.tenant.status')
        );
    }
}
