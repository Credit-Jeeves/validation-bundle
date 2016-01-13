<?php

namespace RentJeeves\PublicBundle\Services;

use CreditJeeves\UserBundle\Security\Encoder\MessageDigestPasswordEncoder;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;

/**
 * DI\Service('tenant.processor')
 */
class TenantProcessor
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var MessageDigestPasswordEncoder
     */
    protected $passwordEncoder;

    /**
     * @var string
     */
    protected $defaultLocale;

    /**
     * @param EntityManager $em
     * @param MessageDigestPasswordEncoder $passwordEncoder
     * @param string $defaultLocale
     */
    public function __construct(EntityManager $em, MessageDigestPasswordEncoder $passwordEncoder, $defaultLocale)
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @param Tenant $tenant New mapped entity from form
     * @param string $password
     * @return Tenant
     */
    public function createNewTenant(Tenant $tenant, $password)
    {
        $password = $this->passwordEncoder->encodePassword($password, $tenant->getSalt());
        $tenant->setPassword($password);
        $tenant->setCulture($this->defaultLocale);
        $this->em->persist($tenant);
        $this->em->flush();

        return $tenant;
    }

    /**
     * @param Tenant $tenant New mapped entity from form
     * @param $password
     * @param ResidentMapping $residentMapping
     * @return Tenant
     */
    public function createNewIntegratedTenant(Tenant $tenant, $password, ResidentMapping $residentMapping)
    {
        $tenant = $this->createNewTenant($tenant, $password);
        $residentMapping->setTenant($tenant);
        $tenant->addResidentsMapping($residentMapping);
        $this->em->persist($residentMapping);
        $this->em->flush();

        return $tenant;
    }
}
