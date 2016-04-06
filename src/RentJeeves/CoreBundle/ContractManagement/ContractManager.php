<?php

namespace RentJeeves\CoreBundle\ContractManagement;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\ContractManagement\Model\ContractDTO;
use RentJeeves\CoreBundle\Exception\ContractCreatorException;
use RentJeeves\CoreBundle\Exception\ContractManagerException;
use RentJeeves\CoreBundle\Exception\UserCreatorException;
use RentJeeves\CoreBundle\UserManagement\UserCreator;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;

class ContractManager
{
    /**
     * @var ContractCreator
     */
    protected $contractCreator;

    /**
     * @var UserCreator
     */
    protected $userCreator;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ContractCreator        $contractCreator
     * @param UserCreator            $userCreator
     * @param EntityManagerInterface $em
     * @param LoggerInterface        $logger
     */
    public function __construct(
        ContractCreator $contractCreator,
        UserCreator $userCreator,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ) {
        $this->contractCreator = $contractCreator;
        $this->userCreator = $userCreator;
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @param Unit        $unit
     * @param ContractDTO $contractDTO
     *
     * @throws ContractManagerException if cant create new Contract
     *
     * @return Contract
     */
    public function createContract(Unit $unit, ContractDTO $contractDTO)
    {
        $this->logger->debug('Try to create new contract.');

        $email = $contractDTO->getEmail();
        $firstName = $contractDTO->getFirstName();
        $lastName = $contractDTO->getLastName();

        if (true === empty($email) && true === empty($firstName) && true === empty($lastName)) {
            $this->logger->warning(
                $message = 'Can`t create new contract without email and firstName and lastName.'
            );
            throw new ContractManagerException($message);
        }

        try {
            // Transactions need for rollback
            // if any service throw exception - we don`t need save any data to db
            $this->em->beginTransaction();

            $tenant = $this->userCreator->createTenant($firstName, $lastName, $email);
            $contract = $this->contractCreator->createContract($unit, $tenant, $contractDTO);

            if (false === empty($contractDTO->getExternalResidentId())) {
                $this->createResidentMapping($contract->getHolding(), $tenant, $contractDTO->getExternalResidentId());
            }

            $this->em->commit();
        } catch (UserCreatorException $e) {
            $this->em->rollback();
            $this->logger->warning($e->getMessage());
            throw new ContractManagerException($e->getMessage());
        } catch (ContractCreatorException $e) {
            $this->em->rollback();
            $this->logger->warning($e->getMessage());
            throw new ContractManagerException($e->getMessage());
        }

        return $contract;
    }

    /**
     * @param Holding $holding
     * @param Tenant  $tenant
     * @param string  $residentId
     */
    protected function createResidentMapping(Holding $holding, Tenant $tenant, $residentId)
    {
        $residentMapping = new ResidentMapping();
        $residentMapping->setHolding($holding);
        $residentMapping->setTenant($tenant);
        $residentMapping->setResidentId($residentId);

        $this->em->persist($residentMapping);
        $this->em->flush();
    }
}
