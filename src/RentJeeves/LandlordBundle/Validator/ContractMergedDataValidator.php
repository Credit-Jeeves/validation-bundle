<?php

namespace RentJeeves\LandlordBundle\Validator;

use CreditJeeves\DataBundle\Enum\UserType;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Validator;
use CreditJeeves\DataBundle\Entity\UserRepository;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractRepository;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyRepository;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\UnitRepository;
use RentJeeves\LandlordBundle\MergingContracts\ContractMergedDTO;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @Validator("contract_merged_data")
 */
class ContractMergedDataValidator extends ConstraintValidator
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var ContractRepository
     */
    protected $contractRepository;

    /**
     * @var PropertyRepository
     */
    protected $propertyRepository;

    /**
     * @var UnitRepository
     */
    protected $unitRepository;

    /**
     * @var string
     */
    protected $supportEmail;

    /**
     * @param EntityManager $em
     * @param string $supportEmail
     * @InjectParams({
     *     "em" = @Inject("doctrine.orm.entity_manager"),
     *     "supportEmail" = @Inject("%support_email%")
     * })
     */
    public function __construct(EntityManager $em, $supportEmail)
    {
        $this->userRepository = $em->getRepository('DataBundle:User');
        $this->contractRepository = $em->getRepository('RjDataBundle:Contract');
        $this->propertyRepository = $em->getRepository('RjDataBundle:Property');
        $this->unitRepository = $em->getRepository('RjDataBundle:Unit');
        $this->supportEmail = $supportEmail;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($mergingDataModel, Constraint $constraint)
    {
        if (!$mergingDataModel instanceof ContractMergedDTO) {
            throw new UnexpectedTypeException($mergingDataModel, 'ContractMergedDTO');
        }

        /** @var Tenant $tenant */
        if ($mergingDataModel->getTenantEmail() &&
            $tenant = $this->userRepository->findOneByEmail($mergingDataModel->getTenantEmail())
        ) {
            if ($tenant->getType() !== UserType::TENANT) {
                $this->context->addViolation(
                    $constraint->messageUserTypeInvalid,
                    [
                        '%correct_user_type%' => UserType::TENANT,
                        '%expected_user_type%' => $tenant->getType()
                    ]
                );
            } elseif (!$tenant->getContracts()->isEmpty() && !$tenant->getContracts()->exists(
                function ($key, Contract $contract) use ($mergingDataModel) {
                return ($contract->getId() === $mergingDataModel->getOriginalContractId() ||
                    $contract->getId() === $mergingDataModel->getDuplicateContractId());
                }
            )
            ) {
                $this->context->addViolation(
                    $constraint->messageUserExist,
                    [
                        '%email%' => $this->formatValue($mergingDataModel->getTenantEmail()),
                        '%user_fullname%' => $tenant->getFullName(),
                        '%support_email%' => $this->supportEmail,
                    ]
                );
            }
        }

        /** @var Contract $contract */
        if ($mergingDataModel->getOriginalContractId() &&
            $contract = $this->contractRepository->find($mergingDataModel->getOriginalContractId())
        ) {
            if ($contract->getGroup()->isAllowedEditResidentId() && !$mergingDataModel->getContractResidentId()) {
                $this->context->addViolation($constraint->messageResidentIdEmpty);
            } elseif ($contract->getGroup()->isAllowedEditLeaseId() && !$mergingDataModel->getContractLeaseId()) {
                $this->context->addViolation($constraint->messageLeaseIdEmpty);
            }
        }
        /** @var Property $property */
        if ($mergingDataModel->getContractPropertyId() &&
            $property = $this->propertyRepository->find($mergingDataModel->getContractPropertyId())
        ) {
            if (!$property->getPropertyAddress()->isSingle() &&
                !$unit = $this->unitRepository
                    ->findBy(['id' => $mergingDataModel->getContractUnitId(), 'property' => $property])
            ) {
                $this->context->addViolation($constraint->messageUnitInvalid);
            }
        } else {
            $this->context->addViolation($constraint->messagePropertyInvalid);
        }
    }
}
