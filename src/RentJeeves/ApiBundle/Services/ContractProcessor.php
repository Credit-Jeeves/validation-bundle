<?php

namespace RentJeeves\ApiBundle\Services;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Enum\GroupType;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\CoreBundle\Services\ContractProcess;
use RentJeeves\CoreBundle\Services\PropertyManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyAddress;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
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
     * @param EntityManager $em
     * @param Mailer $mailer
     * @param $locale
     * @param ContractProcess $contractProcess
     * @param PropertyManager $propertyProcess
     *
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager"),
     *     "mailer" = @DI\Inject("project.mailer"),
     *     "locale" = @DI\Inject("%kernel.default_locale%"),
     *     "contractProcess" = @DI\Inject("contract.process"),
     *     "propertyProcess" = @DI\Inject("property.manager")
     * })
     */
    public function __construct(
        EntityManager $em,
        Mailer $mailer,
        $locale,
        ContractProcess $contractProcess,
        PropertyManager $propertyProcess
    ) {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->locale = $locale;
        $this->contractProcess = $contractProcess;
        $this->propertyManager = $propertyProcess;
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

    public function processWithNewUnit(Form $newUnitForm, Tenant $tenant, Contract $contract)
    {
        /** @var PropertyAddress $propertyAddress */
        $propertyAddress = $newUnitForm->get('address')->getData();
        $unitName = $newUnitForm->get('address')->get('unit_name')->getData();

        $property = $this->findPropertyOrCreateNewAndMapAddressFields($propertyAddress);

        /** @var Landlord $landlord */
        $landlord = $newUnitForm->get('landlord')->getData();

        /** @var Landlord $landlordInDb */
        $landlordInDb = $this->em->getRepository('RjDataBundle:Landlord')->findOneBy([
            'email' => $landlord->getEmail(),
        ]);

        if ($landlordInDb) {
            $landlord = $landlordInDb;
            $group = $landlord->getCurrentGroup();
        } else {
            $landlord->setPassword(md5(md5(1)));
            $landlord->setCulture($this->locale);

            $holding = new Holding();
            $holding->setName($landlord->getUsername());
            $landlord->setHolding($holding);

            $group = new Group();
            $group->setName($landlord->getUsername());
            $group->setType(GroupType::RENT);
            $group->setHolding($holding);

            $holding->addGroup($group);
            $landlord->setAgentGroups($group);

            $this->em->persist($holding);
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

        foreach ($contracts as $contract) {
            if (!$this->mailer->sendRjLandLordInvite($landlord, $tenant, $contract)) {
                throw new \Exception('Email can\'t be send. Please contact with administrator.');
            }
        }

        //TODO return last need understand how it will be fix
        return $contract;
    }

    /**
     * @param PropertyAddress $propertyAddress
     *
     * @throws BadRequestHttpException if address is not valid
     *
     * @return Property
     */
    protected function findPropertyOrCreateNewAndMapAddressFields(PropertyAddress $propertyAddress)
    {
        $property = $this->propertyManager->findPropertyByAddressInDb(
            $propertyAddress->getNumber(),
            $propertyAddress->getStreet(),
            $propertyAddress->getCity(),
            $propertyAddress->getState(),
            $propertyAddress->getZip()
        );

        if (null !== $property) {
            return $property;
        }

        $address = $this->propertyManager->lookupAddress(
            $propertyAddress->getAddress(),
            $propertyAddress->getCity(),
            $propertyAddress->getState(),
            $propertyAddress->getZip()
        );

        if (null === $address) {
            throw new BadRequestHttpException('api.errors.contracts.property.invalid');
        }

        $propertyAddress->setAddressFields($address);

        $newProperty = new Property();
        $newProperty->setPropertyAddress($propertyAddress);

        return $newProperty;
    }
}
