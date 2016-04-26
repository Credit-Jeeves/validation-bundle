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
use RentJeeves\CoreBundle\Services\Exception\PropertyManagerUnitOwnershipException;
use RentJeeves\CoreBundle\Services\PropertyManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\GroupSettings;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\PropertyAddress;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\TrustedLandlordBundle\Exception\TrustedLandlordServiceException;
use RentJeeves\TrustedLandlordBundle\Model\TrustedLandlordDTO;
use RentJeeves\TrustedLandlordBundle\Services\TrustedLandlordService;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @DI\Service("api.contract.processor")
 */
class ContractProcessor
{
    const DEFAULT_STATEMENT_DESCRIPTOR = 'RENTTRK-WSR RENT PAY';

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
     * @see parameter paydirect_fee_cc
     * @var float
     */
    protected $defaultFeeCC;

    /**
     * @see parameter paydirect_fee_ach
     * @var float
     */
    protected $defaultFeeACH;

    /**
     * @see parameter aci.collect_pay.pay_direct_escrow_account
     * @var string
     */
    protected $defaultPayDirectInboundMerchantAccount;

    /**
     * @var int
     */
    protected $defaultMaxLimitPerMonth = 0;

    /**
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param Mailer $mailer
     * @param $locale
     * @param ContractProcess $contractProcess
     * @param PropertyManager $propertyProcess
     * @param TrustedLandlordService $trustedLandlordService
     * @param float $defaultFeeCC
     * @param float $defaultFeeACH
     * @param string $defaultPayDirectInboundMerchantAccount
     * @param int $defaultMaxLimitPerMonth
     *
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager"),
     *     "logger" = @DI\Inject("logger"),
     *     "mailer" = @DI\Inject("project.mailer"),
     *     "locale" = @DI\Inject("%kernel.default_locale%"),
     *     "contractProcess" = @DI\Inject("contract.process"),
     *     "propertyProcess" = @DI\Inject("property.manager"),
     *     "trustedLandlordService" = @DI\Inject("trusted_landlord_service"),
     *     "defaultFeeCC" = @DI\Inject("%paydirect_fee_cc%"),
     *     "defaultFeeACH" = @DI\Inject("%paydirect_fee_ach%"),
     *     "defaultPayDirectInboundMerchantAccount" = @DI\Inject("%aci.collect_pay.pay_direct_escrow_account%"),
     *     "defaultMaxLimitPerMonth" = @DI\Inject("%dod_limit_max_payment%")
     * })
     */
    public function __construct(
        EntityManager $em,
        LoggerInterface $logger,
        Mailer $mailer,
        $locale,
        ContractProcess $contractProcess,
        PropertyManager $propertyProcess,
        TrustedLandlordService $trustedLandlordService,
        $defaultFeeCC,
        $defaultFeeACH,
        $defaultPayDirectInboundMerchantAccount,
        $defaultMaxLimitPerMonth
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->locale = $locale;
        $this->contractProcess = $contractProcess;
        $this->propertyManager = $propertyProcess;
        $this->trustedLandlordService = $trustedLandlordService;
        $this->defaultFeeCC = $defaultFeeCC;
        $this->defaultFeeACH = $defaultFeeACH;
        $this->defaultPayDirectInboundMerchantAccount = $defaultPayDirectInboundMerchantAccount;
        $this->defaultMaxLimitPerMonth = $defaultMaxLimitPerMonth;
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
            $group = $this->createGroup($trustedLandlordDTO);
            $group->setTrustedLandlord($trustedLandlord);
        }

        if ($email = $trustedLandlordDTO->getEmail() and
            !$this->em->getRepository('DataBundle:User')->findOneByEmail($email)
        ) {

            $landlord = $this->createLandlord($trustedLandlordDTO, $group);
            $this->em->persist($landlord);

            if ($tenant->getPartner()) {
                $landlord->setPartner($tenant->getPartner());
            }
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

        if (!$property->isSingle()) {
            try {
                $unit = $this->propertyManager->getOrCreateUnit($group, $property, $unitName);
            } catch (\InvalidArgumentException $e) {
                throw new BadRequestHttpException($e->getMessage(), $e);
            } catch (PropertyManagerUnitOwnershipException $e) {
                throw new HttpException(409, $e->getMessage(), $e);
            }

            $this->em->persist($unit);
            $this->em->flush();
        }

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

        throw new \LogicException('Contract should be created');
    }

    /**
     * @param TrustedLandlordDTO $trustedLandlordDTO
     * @return Group
     */
    protected function createGroup(TrustedLandlordDTO $trustedLandlordDTO)
    {
        $newGroup = new Group();

        $newGroup->setName($this->generateCommercialName($trustedLandlordDTO));
        $newGroup->setHolding($this->createHolding($newGroup->getName()));
        $newGroup->setType(GroupType::RENT);
        $newGroup->setStatementDescriptor(self::DEFAULT_STATEMENT_DESCRIPTOR);
        $newGroup->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);

        $this->createGroupSetting($newGroup);
        $this->createDepositAccount($newGroup);

        return $newGroup;
    }

    /**
     * @param string $holdingName
     * @return Holding
     */
    protected function createHolding($holdingName)
    {
        $newHolding = new Holding();
        $newHolding->setName($holdingName);

        return $newHolding;
    }

    /**
     * @param Group $group
     *
     * @return GroupSettings
     */
    protected function createGroupSetting(Group $group)
    {
        $newGroupSettings = $group->getGroupSettings();
        $newGroupSettings->setPaymentProcessor(PaymentProcessor::ACI);
        $newGroupSettings->setAutoApproveContracts(true);
        $newGroupSettings->setPassedAch(true);
        $newGroupSettings->setFeeCC($this->defaultFeeCC);
        $newGroupSettings->setFeeACH($this->defaultFeeACH);
        $newGroupSettings->setMaxLimitPerMonth($this->defaultMaxLimitPerMonth);

        return $newGroupSettings;
    }

    /**
     * @param Group $group
     *
     * @return DepositAccount
     */
    protected function createDepositAccount(Group $group)
    {
        $newDepositAccount = new DepositAccount($group);
        $newDepositAccount->setType(DepositAccountType::RENT);
        $newDepositAccount->setMerchantName($this->defaultPayDirectInboundMerchantAccount);
        if ($this->defaultPayDirectInboundMerchantAccount) {
            $newDepositAccount->setStatus(DepositAccountStatus::DA_COMPLETE);
        }
        $newDepositAccount->setPaymentProcessor(PaymentProcessor::ACI);

        $group->addDepositAccount($newDepositAccount);

        return $newDepositAccount;
    }

    /**
     * @param TrustedLandlordDTO $trustedLandlordDTO
     * @param Group $group
     * @return Landlord
     */
    protected function createLandlord(TrustedLandlordDTO $trustedLandlordDTO, Group $group)
    {
        $landlord = new Landlord();
        $landlord->setEmail($trustedLandlordDTO->getEmail());
        $landlord->setFirstName($trustedLandlordDTO->getFirstName());
        $landlord->setLastName($trustedLandlordDTO->getLastName());
        $landlord->setPhone($trustedLandlordDTO->getPhone());
        $landlord->setPassword(md5(md5(rand())));
        $landlord->setCulture($this->locale);
        $landlord->setHolding($group->getHolding());
        $landlord->setAgentGroups($group);

        return $landlord;
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
