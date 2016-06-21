<?php

namespace RentJeeves\LandlordBundle\MergingContracts;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\CoreBundle\Traits\ValidateEntities;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Enum\ContractStatus;
use Symfony\Component\Validator\Validator;

class ContractMergingProcessor
{
    use ValidateEntities;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $matchingType = BaseMergingDTO::NO_MATCH_TYPE;

    /**
     * @param EntityManager $em
     * @param Validator $validator
     * @param Mailer $mailer
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, Validator $validator, Mailer $mailer, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    /**
     * @param Contract $contract
     * @param string $email
     * @param string $residentId
     * @throws \LogicException|\InvalidArgumentException
     * @return Contract|null
     */
    public function getOneOrNullDuplicate(Contract $contract, $email = null, $residentId = null)
    {
        $invertedContractStatuses = $this->getInvertedContractStatuses($contract);

        if (empty($invertedContractStatuses)) {
            return null;
        }

        $contractRepo = $this->em->getRepository('RjDataBundle:Contract');

        try {
            if ($email) {
                if ($contract->getUnit()) {
                    $duplicateContractByEmail = $contractRepo
                        ->getOneOrNullDuplicateContractWithUnitByEmail($contract, $email, $invertedContractStatuses);
                }
                if (empty($duplicateContractByEmail)) {
                    $duplicateContractByEmail = $contractRepo
                        ->getOneOrNullDuplicateContractByEmail($contract, $email, $invertedContractStatuses);
                }
                if (!empty($duplicateContractByEmail)) {
                    $this->matchingType = BaseMergingDTO::MATCH_EMAIL_TYPE;
                }
            }

            if ($residentId && $contract->getGroup()->isAllowedEditResidentId()) {
                if ($contract->getUnit()) {
                    $duplicateContractByResident = $contractRepo
                        ->getOneOrNullDuplicateContractWithUnitByResidentId(
                            $contract,
                            $residentId,
                            $invertedContractStatuses
                        );
                }
                if (empty($duplicateContractByResident)) {
                    $duplicateContractByResident = $contractRepo
                        ->getOneOrNullDuplicateContractByResidentId(
                            $contract,
                            $residentId,
                            $invertedContractStatuses
                        );
                }
                if (!empty($duplicateContractByResident)) {
                    $this->matchingType = BaseMergingDTO::MATCH_RESIDENT_TYPE;
                }
            }
        } catch (NonUniqueResultException $e) {
            throw new \LogicException('Found more then one duplicate contracts');
        }

        if (!empty($duplicateContractByEmail) &&
            !empty($duplicateContractByResident) &&
            $duplicateContractByResident->getId() !== $duplicateContractByEmail->getId()
        ) {
            throw new \LogicException('Found different duplicate contracts by both parameters');
        }

        return !empty($duplicateContractByEmail) ? $duplicateContractByEmail :
            (!empty($duplicateContractByResident) ? $duplicateContractByResident : null);
    }

    /**
     * @param Contract $originalContract
     * @param Contract $duplicateContract
     * @return BaseMergingDTO
     */
    public function getMergingContractData(Contract $originalContract, Contract $duplicateContract)
    {
        $invertedContractStatuses = $this->getInvertedContractStatuses($originalContract);

        if (empty($invertedContractStatuses)) {
            throw new \LogicException('Contracts should have "pending", "invite" or "waiting" statuses');
        }

        if (!in_array($duplicateContract->getStatus(), $invertedContractStatuses)) {
            throw new \LogicException('One contract should has status "pending" other one "invite" or "waiting"');
        }

        if ($originalContract->getStatus() == ContractStatus::PENDING) {
            $mergingModel = new TenantOriginatedMergingDTO($originalContract, $duplicateContract);
        } else {
            $mergingModel = new LeaseOriginatedMergingDTO($originalContract, $duplicateContract);
        }
        $mergingModel->setMatchingType($this->matchingType);

        return $mergingModel;
    }

    /**
     * @param Contract $originalContract
     * @param Contract $duplicateContract
     * @param ContractMergedDTO $mergingModel
     * @return boolean
     */
    public function mergeContracts(
        Contract $originalContract,
        Contract $duplicateContract,
        ContractMergedDTO $mergingModel
    ) {
        if ($originalContract->getStatus() == ContractStatus::PENDING) {
            $tenantDataContract = $originalContract;
            $leaseDataContract = $duplicateContract;
        } else {
            $tenantDataContract = $duplicateContract;
            $leaseDataContract = $originalContract;
        }

        $tenantToRemove = $leaseDataContract->getTenant();
        $tenantToMerge = $tenantDataContract->getTenant();
        $mergedResidentMapping = $tenantToRemove->getResidentForHolding($leaseDataContract->getHolding());

        $tenantToMerge->setFirstName($mergingModel->getTenantFirstName());
        $tenantToMerge->setLastName($mergingModel->getTenantLastName());
        $tenantToMerge->setEmail($mergingModel->getTenantEmail());
        $tenantToMerge->setPhone($mergingModel->getTenantPhone());

        if ($leaseDataContract->getGroup()->isAllowedEditResidentId()) {
            if (!$mergedResidentMapping) {
                $mergedResidentMapping = new ResidentMapping();
                $mergedResidentMapping->setHolding($leaseDataContract->getHolding());
            }
            $mergedResidentMapping->setTenant($tenantToMerge);
            $mergedResidentMapping->setResidentId($mergingModel->getContractResidentId());
        }

        $leaseDataContract->setTenant($tenantToMerge);

        if ($leaseDataContract->getGroup()->isAllowedEditLeaseId()) {
            $leaseDataContract->setExternalLeaseId($mergingModel->getContractLeaseId());
        }
        if ($mergingModel->getContractPropertyId()) {
            $property = $this->em->find('RjDataBundle:Property', $mergingModel->getContractPropertyId());
            $leaseDataContract->setProperty($property);
            if ($property->getPropertyAddress()->isSingle()) {
                $leaseDataContract->setUnit($property->getExistingSingleUnit());
            }
        }
        if ($mergingModel->getContractUnitId()) {
            $unit = $this->em->find('RjDataBundle:Unit', $mergingModel->getContractUnitId());
            $leaseDataContract->setUnit($unit);
        }
        if ($leaseDataContract->getGroupSettings()->getIsIntegrated()) {
            $leaseDataContract->setIntegratedBalance($mergingModel->getContractIntegratedBalance());
        }

        $leaseDataContract->setRent($mergingModel->getContractRent());
        $leaseDataContract->setDueDate($mergingModel->getContractDueDate());
        $leaseDataContract->setStartAt($mergingModel->getContractStartAt());
        $leaseDataContract->setFinishAt($mergingModel->getContractFinishAt());
        $leaseDataContract->setStatusApproved();

        $this->validate($leaseDataContract, "merging");

        if ($this->hasErrors()) {
            return false;
        }

        $this->em->flush();
        $this->mailer->sendContractApprovedToTenant($leaseDataContract);
        $this->em->refresh($tenantToRemove);

        if ($tenantToRemove->getContracts()->isEmpty()  &&
            ($tenantToRemove->getResidentsMapping()->isEmpty() || empty($tenantToRemove->getEmail()))
        ) {
            $this->em->remove($tenantToRemove);
        }

        $this->em->remove($tenantDataContract);
        $this->em->flush();

        return true;
    }

    /**
     * @param Contract $contract
     * @return array
     */
    protected function getInvertedContractStatuses(Contract $contract)
    {
        if (ContractStatus::WAITING === $contract->getStatus() || ContractStatus::INVITE === $contract->getStatus()) {
            return [ContractStatus::PENDING];
        } elseif (ContractStatus::PENDING === $contract->getStatus()) {
            return [ContractStatus::WAITING, ContractStatus::INVITE];
        }

        return [];
    }
}
