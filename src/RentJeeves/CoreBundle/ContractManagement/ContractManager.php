<?php

namespace RentJeeves\CoreBundle\ContractManagement;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\ContractManagement\Model\ContractDTO;
use RentJeeves\CoreBundle\ContractManagement\Model\UserDTO;
use RentJeeves\CoreBundle\Exception\ContractCreatorException;
use RentJeeves\CoreBundle\Exception\ContractManagerException;
use RentJeeves\CoreBundle\Exception\UserCreatorException;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\CoreBundle\UserManagement\UserCreator;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator;

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
     * @var Validator
     */
    protected $validator;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @param ContractCreator        $contractCreator
     * @param UserCreator            $userCreator
     * @param EntityManagerInterface $em
     * @param LoggerInterface        $logger
     * @param Validator              $validator
     * @param Mailer                 $mailer
     */
    public function __construct(
        ContractCreator $contractCreator,
        UserCreator $userCreator,
        EntityManagerInterface $em,
        LoggerInterface $logger,
        Validator $validator,
        Mailer $mailer
    ) {
        $this->contractCreator = $contractCreator;
        $this->userCreator = $userCreator;
        $this->em = $em;
        $this->logger = $logger;
        $this->validator = $validator;
        $this->mailer = $mailer;
    }

    /**
     * @param Unit        $unit
     * @param UserDTO     $userDTO
     * @param ContractDTO $contractDTO
     *
     * @throws ContractManagerException if cant create new Contract
     *
     * @return Contract
     */
    public function createContract(Unit $unit, UserDTO $userDTO, ContractDTO $contractDTO)
    {
        $this->logger->debug('Try to create new contract.');

        $email = $userDTO->getEmail();
        $firstName = $userDTO->getFirstName();
        $lastName = $userDTO->getLastName();

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

            if (true === $userDTO->isSupportResidentId() && false === empty($userDTO->getExternalResidentId())) {
                $this->createResidentMapping($contract->getHolding(), $tenant, $userDTO->getExternalResidentId());
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
     * @param Contract $contract
     * @param string   $newStatus
     * @param null     $tenantEmail
     *
     * @throws \LogicException Contract has not Waiting status
     */
    public function moveContractOutOfWaiting(
        Contract $contract,
        $newStatus = ContractStatus::APPROVED,
        $tenantEmail = null
    ) {
        $this->logger->debug(
            sprintf('Try to move Contract#%d from waiting status to %s.', $contract->getId(), $newStatus)
        );

        if ($contract->getStatus() !== ContractStatus::WAITING) {
            throw new \LogicException(
                sprintf('Cant use function %s for contract with status %s', __FUNCTION__, $contract->getStatus())
            );
        }

        $contract->setStatus($newStatus);

        if (false === empty($tenantEmail) && $this->isValidEmail($tenantEmail)) {
            $tenant = $contract->getTenant();
            $tenant->setEmailField($tenantEmail);
            $tenant->setEmailCanonical(strtolower($tenantEmail));
            $tenant->setEmailNotification(true);
            $tenant->setOfferNotification(true);

            $this->sendInviteToTenant($tenant, $contract);
        }

        $this->em->flush();
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

    /**
     * @param string $email
     *
     * @return bool
     */
    protected function isValidEmail($email)
    {
        $emailConstraint = new Assert\Email();
        $errorList = $this->validator->validate($email, $emailConstraint);
        if (0 !== count($errorList)) {
            $this->logger->warning('"%s" is not valid email.', $email);

            return false;
        }

        return true;
    }

    /**
     * @param Tenant   $tenant
     * @param Contract $contract
     */
    protected function sendInviteToTenant(Tenant $tenant, Contract $contract)
    {
        $landlord = $contract->getHolding()->getLandlords()->first();

        if (true === empty($landlord)) {
            $this->logger->warning(
                sprintf(
                    'We can`t find landlord for Group#%d. Skip send email.',
                    $contract->getGroup()->getId()
                )
            );

            return;
        }

        $this->mailer->sendRjTenantInvite($tenant, $landlord, $contract);
    }
}
