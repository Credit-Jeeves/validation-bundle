<?php

namespace RentJeeves\LandlordBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class ContractMergedData extends Constraint
{
    public $messageUserTypeInvalid = 'user.error.type.invalid';

    public $messageUserExist = 'contract.error.email.exist';

    public $messageResidentIdEmpty = 'contract.merging.error.resident.empty';

    public $messageLeaseIdEmpty = 'contract.merging.error.lease.empty';

    public $messageUnitInvalid = 'contract.merging.error.unit.invalid';

    public $messagePropertyInvalid = 'contract.merging.error.property.invalid';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'contract_merged_data';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
