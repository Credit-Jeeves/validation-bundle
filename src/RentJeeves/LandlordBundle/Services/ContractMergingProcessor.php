<?php

namespace RentJeeves\LandlordBundle\Services;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;

class ContractMergingProcessor
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param EntityManager $em
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, LoggerInterface $logger)
    {
        $this->em = $em;
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
            if ($residentId && $contract->getGroup()->isAllowedEditResidentId()) {
                $duplicateContractByResident = $contractRepo
                    ->getOneOrNullDuplicateContractByResidentId($contract, $residentId, $invertedContractStatuses);
            }

            if ($email) {
                $duplicateContractByEmail = $contractRepo
                    ->getOneOrNullDuplicateContractByEmail($contract, $email, $invertedContractStatuses);
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

    public function getMergingContractData(Contract $firstContract, Contract $secondContract)
    {
        $invertedContractStatuses = $this->getInvertedContractStatuses($firstContract);

        if (empty($invertedContractStatuses)) {
            throw new \LogicException('Contracts should have "pending", "invite" or "waiting" statuses');
        }

        if (!in_array($secondContract->getStatus(), $invertedContractStatuses)) {
            throw new \LogicException('One contract should has status "pending" other one "invite" or "waiting"');
        }

        if ($firstContract->getStatus() == ContractStatus::PENDING) {
            return new ContractMergedDTO($firstContract, $secondContract);
        }

        return new ContractMergedDTO($secondContract, $firstContract);
    }

    public function mergeContracts(ContractMergedDTO $contractModel)
    {

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
