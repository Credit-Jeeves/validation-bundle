<?php

namespace RentJeeves\CoreBundle\Services;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\CoreBundle\Traits\ValidateEntities;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
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

    use ValidateEntities;

    protected $em;

    protected $contract;

    protected $isValidateContract = false;

    /**
     * @InjectParams({
     *     "em" = @Inject("doctrine.orm.default_entity_manager"),
     *     "validator" = @Inject("validator")
     * })
     */
    public function __construct(EntityManager $em, $validator)
    {
        $this->em = $em;
        $this->validator = $validator;
    }

    public function setContract(Contract $contract)
    {
        $this->contract = $contract;

        return $this;
    }

    public function setIsValidateContract($isValidateContract)
    {
        $this->isValidateContract = !!$isValidateContract;
    }

    /**
     * @param Tenant $tenant
     * @param Property $property
     * @param null $unitName
     * @param ContractWaiting $contractWaiting
     *
     * @return Contract|void
     */
    public function createContractFromTenantSide(
        Tenant $tenant,
        Property $property,
        $unitName = null,
        ContractWaiting $contractWaiting = null
    ) {

        $contract = $this->contract ?: new Contract();
        $contract->setTenant($tenant);
        $contract->setProperty($property);
        $contract->setStatus(ContractStatus::PENDING);

        /**
         * @var $contractWaiting ContractWaiting
         */
        if (empty($contractWaiting)) {
            if ($property->isSingle()) {
                $propertyGroup = $property->getPropertyGroups()->first();
                $contract->setHolding($propertyGroup->getHolding());
                $contract->setGroup($propertyGroup);
                $contract->setUnit($property->getExistingSingleUnit());
            } else {
                if (Unit::SEARCH_UNIT_UNASSIGNED === $unitName || !$unit = $property->searchUnit($unitName)) {
                    return $this->createContractForEachGroup($tenant, $property, $unitName);
                }

                $contract->setHolding($unit->getHolding());
                $contract->setGroup($unit->getGroup());
                $contract->setUnit($unit);
            }

            !$this->isValidateContract || $this->validate($contract);

            if ($this->hasErrors()) {
                return false;
            }

            if (null === $contract->getDueDate()) {
                $contract->setDueDate($contract->getGroup()->getGroupSettings()->getDueDate());
            }

            $this->em->persist($contract);
            $this->em->flush();

            return $contract;
        }

        $contract->setExternalLeaseId($contractWaiting->getExternalLeaseId());

        return $this->createContractFromWaiting($tenant, $contractWaiting);
    }

    /**
     * @param Tenant $tenant
     * @param ContractWaiting $contractWaiting
     *
     * @return Contract
     */
    public function createContractFromWaiting(Tenant $tenant, ContractWaiting $contractWaiting)
    {
        $contract = new Contract();
        $contract->setTenant($tenant);
        $contract->setProperty($contractWaiting->getProperty());
        $tenant = $contract->getTenant();
        $contract->setHolding($contractWaiting->getGroup()->getHolding());
        $contract->setGroup($contractWaiting->getGroup());
        $contract->setUnit($contractWaiting->getUnit());
        $contract->setStatus(ContractStatus::APPROVED);
        $contract->setStartAt($contractWaiting->getStartAt());
        $contract->setDueDate($contract->getGroup()->getGroupSettings()->getDueDate());
        $contract->setFinishAt($contractWaiting->getFinishAt());
        $contract->setIntegratedBalance($contractWaiting->getIntegratedBalance());
        $contract->setRent($contractWaiting->getRent());
        $contract->setPaymentAccepted($contractWaiting->getPaymentAccepted());
        $contract->setExternalLeaseId($contractWaiting->getExternalLeaseId());
        $this->em->persist($contract);

        $group = $contractWaiting->getGroup();
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

        !$this->isValidateContract || $this->validate($contract);

        if ($this->hasErrors()) {
            return false;
        }

        $this->em->remove($contractWaiting);
        $this->em->flush();

        return $contract;
    }

    /**
     * @param Tenant $tenant
     * @param Property $property
     * @param $unitName
     *
     * @todo Need fix this
     *
     * @return array<Contract>
     */
    public function createContractForEachGroup(Tenant $tenant, Property $property, $unitName)
    {
        $result = [];
        // If there is no such unit we'll send contract for all potential landlords
        $groups = $property->getPropertyGroups();
        $contract = $this->contract ? clone $this->contract : new Contract();
        $contract->setTenant($tenant);
        $contract->setProperty($property);
        $contract->setStatus(ContractStatus::PENDING);
        $contract->setSearch($unitName);

        // can be created duplicate contract for each group only first time
        !$this->isValidateContract || $this->validate($contract);

        if ($this->hasErrors()) {
            return false;
        }

        /** @var Group $group */
        foreach ($groups as $group) {
            $contract->setHolding($group->getHolding());
            $contract->setGroup($group);
            if (null === $contract->getDueDate()) {
                $contract->setDueDate($contract->getGroup()->getGroupSettings()->getDueDate());
            }
            $this->em->persist($contract);
            $result[] = $contract;
            $contract = clone $contract;
        }

        $this->em->flush();

        return $result;
    }
}
