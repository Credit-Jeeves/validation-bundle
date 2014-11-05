<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\EntityManager;


use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant as EntityTenant;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;

trait Tenant
{

    protected $userEmails = array();

    /**
     * @param array $row
     *
     * @return EntityTenant
     */
    protected function getTenant(array $row)
    {
        /**
         * @var $tenant EntityTenant
         */
        $tenant = $this->em->getRepository('RjDataBundle:Tenant')->getTenantForImportWithResident(
            $row[Mapping::KEY_EMAIL],
            $row[Mapping::KEY_RESIDENT_ID],
            $this->user->getHolding()->getId()
        );

        if (!empty($tenant)) {
            /**
             * @var $residentMapping ResidentMapping
             */
            $residentMapping = $tenant->getResidentsMapping()->first();

            if ($residentMapping && $residentMapping->getResidentId() !== $row[Mapping::KEY_RESIDENT_ID]) {
                $tenant = $this->createTenant($row);
                $this->fillUsersEmail($tenant); //Make it error, because resident ID different
                return $tenant;
            }

            $this->fillUsersEmail($tenant);
            return $tenant;
        }

        return $this->createTenant($row);
    }

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

        $tenant->setEmail($row[Mapping::KEY_EMAIL]);
        $tenant->setEmailCanonical($row[Mapping::KEY_EMAIL]);
        $tenant->setPassword(md5(md5(1)));
        $tenant->setCulture($this->locale);
        $this->fillUsersEmail($tenant);

        return $tenant;
    }


    protected function fillUsersEmail(EntityTenant $tenant)
    {
        $email = $tenant->getEmail();
        if (empty($email)) {
            return;
        }

        if (isset($this->userEmails[$email])) {
            $this->userEmails[$email]++;
            return;
        }

        $this->userEmails[$email] = 1;
    }
}
