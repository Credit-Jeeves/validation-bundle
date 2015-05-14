<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\EntityManager;

use RentJeeves\CoreBundle\Services\PhoneNumberFormatter;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant as EntityTenant;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract;
use RentJeeves\LandlordBundle\Model\Import;

/**
 * @property Import currentImportModel
 */
trait Tenant
{
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
        $this->checkTenantStatus($row);
        /** @var EntityTenant $tenant */
        $tenant = $this->em->getRepository('RjDataBundle:Tenant')->getTenantForImportWithResident(
            $row[Mapping::KEY_EMAIL],
            $row[Mapping::KEY_RESIDENT_ID],
            $this->user->getHolding()->getId()
        );

        if (!empty($tenant)) {
            /** @var $residentMapping ResidentMapping */
            $residentMapping = $tenant->getResidentsMapping()->first();
            if ($residentMapping && $residentMapping->getResidentId() !== $row[Mapping::KEY_RESIDENT_ID]) {
                $tenant = $this->createTenant($row);
                $this->currentImportModel->setTenant($tenant);
                $this->userEmails[$tenant->getEmail()] = 2; //Make it error, because resident ID different

                return;
            }
            $this->currentImportModel->setTenant($tenant);
            $this->fillUsersEmailAndResident($tenant, $row);

            return;
        }

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
        if (!isset($row[Mapping::FIRST_NAME_TENANT]) && !isset($row[Mapping::LAST_NAME_TENANT])) {
            $names = Mapping::parseName($row[Mapping::KEY_TENANT_NAME]);
            $tenant->setFirstName($names[Mapping::FIRST_NAME_TENANT]);
            $tenant->setLastName($names[Mapping::LAST_NAME_TENANT]);
        } else {
            $tenant->setFirstName($row[Mapping::FIRST_NAME_TENANT]);
            $tenant->setLastName($row[Mapping::LAST_NAME_TENANT]);
        }

        if (!empty($row[Mapping::KEY_USER_PHONE])) {
            $this->setUserPhone($tenant, $row[Mapping::KEY_USER_PHONE]);
        }

        $tenant->setEmail($row[Mapping::KEY_EMAIL]);
        $tenant->setEmailCanonical($row[Mapping::KEY_EMAIL]);
        $tenant->setPassword(md5(md5(1)));
        $tenant->setCulture($this->locale);

        return $tenant;
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
     * @param array $row
     */
    protected function checkTenantStatus(array $row)
    {
        if (!isset($row[MappingAbstract::KEY_TENANT_STATUS])) {
            return;
        }

        if (trim(strtolower($row[MappingAbstract::KEY_TENANT_STATUS])) === 'c') {
            return;
        }

        $this->currentImportModel->setIsSkipped(true);
        $this->currentImportModel->setSkippedMessage(
            $this->translator->trans('error.tenant.status')
        );
    }
}
