<?php

namespace RentJeeves\DataBundle\Validators;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Validator;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractRepository;
use RentJeeves\DataBundle\Enum\ContractStatus;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * @Validator("duplicate_contract_validator")
 */
class ContractDuplicateValidator extends ConstraintValidator
{
    protected $em;

    protected $disallowContractDuplicate;

    /**
     * @InjectParams({
     *     "em" = @Inject("doctrine.orm.entity_manager")
     *     "disallowContractDuplicate" = @@Inject("disallow_contract_duplicate")
     * })
     */
    public function __construct(EntityManager $em, $disallowContractDuplicate)
    {
        $this->em = $em;
        $this->disallowContractDuplicate = $disallowContractDuplicate;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($object, Constraint $constraint)
    {
        if (!$object instanceof Contract) {
            throw new ValidatorException('This validator can work only with Contract Entity');
        }

        if (!$this->disallowContractDuplicate) {
            return;
        }

        if ($object->getStatus() == ContractStatus::DELETED || $object->getStatus() == ContractStatus::FINISHED) {
            return;
        }

        if (!$object->getTenant() || !$object->getTenant()->getId() || !$object->getProperty()) {
            return;
        }

        /** @var ContractRepository $repo */
        $repo = $this->em->getRepository('RjDataBundle:Contract');
        if ($object->getUnit() &&
            $repo->isExistDuplicateByTenantUnit($object->getTenant(), $object->getUnit(), $object->getId())
        ) {
            $this->context->addViolation($constraint->duplicateMessage);
        } elseif (!$object->getUnit() &&
            $repo->isExistDuplicateByTenantPropertyUnitname(
                $object->getTenant(),
                $object->getProperty(),
                $object->getSearch(),
                $object->getId()
            )
        ) {

            $this->context->addViolation($constraint->duplicateMessage);
        }
    }
}
