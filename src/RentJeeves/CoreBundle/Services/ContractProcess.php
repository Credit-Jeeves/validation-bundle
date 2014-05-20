<?php

namespace RentJeeves\CoreBundle\Services;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Contract;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("contract.process")
 */
class ContractProcess
{

    protected $em;

    /**
     * @InjectParams({
     *     "em" = @Inject("doctrine.orm.default_entity_manager"),
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function createContractFromTenantSide(
        Tenant $tenant,
        Property $property,
        $unitName = null,
        ContractWaiting $contractWaiting = null
    ) {

        if ($property->isSingle()) {
            $propertyGroup = $property->getPropertyGroups()->first();
            $contract = new Contract();
            $contract->setTenant($tenant);
            $contract->setHolding($propertyGroup->getHolding());
            $contract->setGroup($propertyGroup);
            $contract->setProperty($property);
            $contract->setStatus(ContractStatus::PENDING);
            $this->em->persist($contract);
            $this->em->flush();
            return;
        }


        if (!$unit = $property->searchUnit($unitName)) {
            return $this->createContractForEachGroup($tenant, $property, $unitName);
        }

        $contract = new Contract();
        $contract->setTenant($tenant);
        $contract->setHolding($unit->getHolding());
        $contract->setGroup($unit->getGroup());
        $contract->setProperty($unit->getProperty());
        $contract->setStatus(ContractStatus::PENDING);
        $contract->setUnit($unit);
        $this->em->persist($contract);
        /**
         * @var $contractWaiting ContractWaiting
         */
        if (empty($contractWaiting)) {
            $this->em->flush();
            return;
        }

        $contract->setStatus(ContractStatus::APPROVED);
        $contract->setStartAt($contractWaiting->getStartAt());
        $contract->setFinishAt($contractWaiting->getFinishAt());
        $contract->setImportedBalance($contractWaiting->getImportedBalance());
        $contract->setRent($contractWaiting->getRent());

        $group = $contract->getUnit()->getGroup();
        $hasResident = true;
        /**
         * On the database level it can be null, so we must check
         */
        if (!empty($group) && $holding = $group->getHolding()) {
            $hasResident = $tenant->hasResident(
                $holding,
                $contractWaiting->getResidentId()
            );
        }

        if (!$hasResident) {
            $residentMapping = new ResidentMapping();
            $residentMapping->setResidentId($contractWaiting->getResidentId());
            $residentMapping->setHolding($holding);
            $residentMapping->setTenant($tenant);
            $this->em->persist($residentMapping);
        }

        $this->em->remove($contractWaiting);
        $this->em->flush();
    }

    /**
     * @param Tenant $tenant
     * @param Property $property
     * @param $unitName
     */
    public function createContractForEachGroup(Tenant $tenant, Property $property, $unitName)
    {
        // If there is no such unit we'll send contract for all potential landlords
        $groups = $property->getPropertyGroups();
        foreach ($groups as $group) {
            $contract = new Contract();
            $contract->setTenant($tenant);
            $contract->setHolding($group->getHolding());
            $contract->setGroup($group);
            $contract->setProperty($property);
            $contract->setStatus(ContractStatus::PENDING);
            $contract->setSearch($unitName);
            $this->em->persist($contract);
        }

        $this->em->flush();
    }
}
