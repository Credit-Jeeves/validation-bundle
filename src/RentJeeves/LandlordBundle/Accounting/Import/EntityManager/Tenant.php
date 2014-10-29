<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\EntityManager;


use RentJeeves\DataBundle\Entity\Tenant as EntityTenant;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;

trait Tenant
{

    protected $usersEmail = array();

    /**
     * @param array $row
     *
     * @return EntityTenant
     */
    protected function getTenant(array $row)
    {
        $tenant = $this->em->getRepository('RjDataBundle:Tenant')->getTenantForImportWithResident(
            $row[Mapping::KEY_EMAIL],
            $row[Mapping::KEY_RESIDENT_ID],
            $this->user->getHolding()->getId()
        );

        if (!empty($tenant)) {
            $this->fillUsersEmail($tenant);
            return $tenant;
        }

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

        if (isset($this->usersEmail[$email])) {
            $this->usersEmail[$email]++;
            return;
        }

        $this->usersEmail[$email] = 1;
    }
}
