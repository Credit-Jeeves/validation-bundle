<?php

namespace RentJeeves\ApiBundle\Services;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\GroupType;
use CreditJeeves\DataBundle\Enum\UserType;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\CoreBundle\Services\ContractProcess;
use RentJeeves\CoreBundle\Services\PropertyManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\PropertyAddress;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\TrustedLandlordType;
use RentJeeves\TrustedLandlordBundle\Exception\TrustedLandlordServiceException;
use RentJeeves\TrustedLandlordBundle\Model\TrustedLandlordDTO;
use RentJeeves\TrustedLandlordBundle\Services\TrustedLandlordService;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @DI\Service("api.contract.processor")
 */
class ContractProcessor
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
     * @var string
     */
    protected $locale;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var ContractProcess
     */
    protected $contractProcess;

    /**
     * @var PropertyManager
     */
    protected $propertyManager;

    /**
     * @var TrustedLandlordService
     */
    protected $trustedLandlordService;

    /**
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param Mailer $mailer
     * @param $locale
     * @param ContractProcess $contractProcess
     * @param PropertyManager $propertyProcess
     * @param TrustedLandlordService $trustedLandlordService
     *
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager"),
     *     "logger" = @DI\Inject("logger"),
     *     "mailer" = @DI\Inject("project.mailer"),
     *     "locale" = @DI\Inject("%kernel.default_locale%"),
     *     "contractProcess" = @DI\Inject("contract.process"),
     *     "propertyProcess" = @DI\Inject("property.manager"),
     *     "trustedLandlordService" = @DI\Inject("trusted_landlord_service")
     * })
     */
    public function __construct(
        EntityManager $em,
        LoggerInterface $logger,
        Mailer $mailer,
        $locale,
        ContractProcess $contractProcess,
        PropertyManager $propertyProcess,
        TrustedLandlordService $trustedLandlordService
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->locale = $locale;
        $this->contractProcess = $contractProcess;
        $this->propertyManager = $propertyProcess;
        $this->trustedLandlordService = $trustedLandlordService;
    }

    /**
     * @param Form $contractForm
     * @param Tenant $tenant
     *
     * @throws \Exception
     *
     * @return Contract
     */
    public function process(Form $contractForm, Tenant $tenant)
    {
        if ($contractForm->has('unit_url') && $unit = $contractForm->get('unit_url')->getData()) {
            $contract = $this->processWithExistUnit($unit, $tenant, $contractForm->getData());
        } elseif ($contractForm->has('new_unit') && $newUnitForm = $contractForm->get('new_unit')) {
            $contract = $this->processWithNewUnit($newUnitForm, $tenant, $contractForm->getData());
        } else {
            throw new \Exception('Contract can\'t be processed.');
        }

        if ($contract->getGroupSettings()->isAutoApproveContracts() === true) {
            $contract->setStatus(ContractStatus::APPROVED);

            $this->em->flush($contract);
        }

        return $contract;
    }

    /**
     * @param Unit $unit
     * @param Tenant $tenant
     * @param Contract $contract
     *
     * @return Contract|void
     *
     * @throws \Exception
     */
    public function processWithExistUnit(Unit $unit, Tenant $tenant, Contract $contract)
    {
        $contract = $this
            ->contractProcess
            ->setContract($contract)
            ->createContractFromTenantSide($tenant, $unit->getProperty(), $unit->getName());

        $group = $unit->getGroup() ?: $unit->getProperty()->getPropertyGroups()->first();

        foreach ($group->getGroupAgents() as $landlord) {
            if (!$this->mailer->sendRjLandLordInvite($landlord, $tenant, $contract)) {
                throw new \Exception('Email can\'t be send. Please contact with administrator.');
            }
        }

        return $contract;
    }

    /**
     * @param Form $newUnitForm
     * @param Tenant $tenant
     * @param Contract $contract
     *
     * @throws \Exception Error sending message
     * @throws BadRequestHttpException Error creating or editing Property
     *
     * @return Contract
     */
    public function processWithNewUnit(Form $newUnitForm, Tenant $tenant, Contract $contract)
    {
        /** @var PropertyAddress $propertyAddress */
        $propertyAddress = $newUnitForm->get('address')->getData();
        $unitName = $newUnitForm->get('address')->get('unit_name')->getData();

        $property = $this->propertyManager->getOrCreatePropertyByAddressFields(
            $propertyAddress->getNumber(),
            $propertyAddress->getStreet(),
            $propertyAddress->getCity(),
            $propertyAddress->getState(),
            $propertyAddress->getZip()
        );

        if (null === $property) {
            throw new BadRequestHttpException('api.errors.contracts.property.invalid');
        }

        /** @var TrustedLandlordDTO $trustedLandlordDTO */
        $trustedLandlordDTO = $newUnitForm->get('landlord')->getData();
        if (!$trustedLandlord = $this->trustedLandlordService->lookup($trustedLandlordDTO)) {
            try {
                $trustedLandlord = $this->trustedLandlordService->create($trustedLandlordDTO);
            } catch (TrustedLandlordServiceException $e) {
                throw new BadRequestHttpException('api.errors.contracts.mailing_address.invalid');
            }
        }

        if (!$group = $trustedLandlord->getGroup()) {
            $holding = new Holding();
            $holding->setName($this->generateCommercialName($trustedLandlordDTO));

            $group = new Group();
            $group->setName($this->generateCommercialName($trustedLandlordDTO));
            $group->setType(GroupType::RENT);
            $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
            $group->setHolding($holding);
            $group->setTrustedLandlord($trustedLandlord);

            $holding->addGroup($group);

            $this->em->persist($holding);
        }

        if ($email = $trustedLandlordDTO->getEmail() and
            !$this->em->getRepository('DataBundle:User')->findOneByEmail($email)
        ) {
            $landlord = new Landlord();
            $landlord->setEmail($email);
            $landlord->setFirstName($trustedLandlordDTO->getFirstName());
            $landlord->setLastName($trustedLandlordDTO->getLastName());
            $landlord->setPhone($trustedLandlordDTO->getPhone());
            $landlord->setPassword(md5(md5(1)));
            $landlord->setCulture($this->locale);
            $landlord->setHolding($group->getHolding());
            $landlord->setAgentGroups($group);

            $this->em->persist($landlord);
        }

        $group->addGroupProperty($property);
        $property->addPropertyGroup($group);

        if (!$property->getId() && !$unitName) {
            $unit = $this->propertyManager->setupSingleProperty($property, ['doFlush' => false]);
            $this->em->persist($unit);
        } elseif ($property->getId() && !$propertyAddress->isSingle() && !$unitName) {
            throw new BadRequestHttpException('api.errors.contracts.property.not_standalone');
        }

        $this->em->persist($property);
        $this->em->persist($group);
        $this->em->flush();

        $contracts = $this
            ->contractProcess
            ->setContract($contract)
            ->createContractFromTenantSide($tenant, $property, $unitName);
        if (!is_array($contracts)) {
            $contracts = [$contracts];
        }
        /** @var Contract[] $contracts */
        foreach ($contracts as $contract) {
            if ($group->getId() == $contract->getGroup()->getId()) {
                if (!empty($landlord) && !$this->mailer->sendRjLandLordInvite($landlord, $tenant, $contract)) {
                    throw new \Exception(
                        sprintf(
                            'Invitation email to "%s" can\'t be send. Please contact with administrator.',
                            $landlord->getEmail()
                        )
                    );
                }

                return $contract;
            }
        }

        //TODO return last need understand how it will be fix
        return $contract;
    }

    /**
     * @param TrustedLandlordDTO $trustedLandlordDTO
     * @return string
     */
    protected function generateCommercialName(TrustedLandlordDTO $trustedLandlordDTO)
    {
        return $trustedLandlordDTO->getCompanyName() ?:
            sprintf('%s %s', $trustedLandlordDTO->getFirstName(), $trustedLandlordDTO->getLastName());
    }
}
