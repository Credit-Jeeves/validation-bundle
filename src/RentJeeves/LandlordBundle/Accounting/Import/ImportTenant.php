<?php

namespace RentJeeves\LandlordBundle\Accounting\Import;


use RentJeeves\DataBundle\Entity\Tenant;

trait ImportTenant
{
    /**
     * @param array $row
     *
     * @return Tenant
     */
    protected function getTenant(array $row)
    {
        $tenant = $this->em->getRepository('RjDataBundle:Tenant')->getTenantForImportWithResident(
            $row[ImportMapping::KEY_EMAIL],
            $row[ImportMapping::KEY_RESIDENT_ID],
            $this->user->getHolding()->getId()
        );

        if (!empty($tenant)) {
            return $tenant;
        }

        $tenant = new Tenant();
        $names = ImportMapping::parseName($row[ImportMapping::KEY_TENANT_NAME]);
        $tenant->setFirstName($names[ImportMapping::FIRST_NAME_TENANT]);
        $tenant->setLastName($names[ImportMapping::LAST_NAME_TENANT]);
        $tenant->setEmail($row[ImportMapping::KEY_EMAIL]);
        $tenant->setEmailCanonical($row[ImportMapping::KEY_EMAIL]);
        $tenant->setPassword(md5(md5(1)));
        $tenant->setCulture($this->locale);

        return $tenant;
    }
}
