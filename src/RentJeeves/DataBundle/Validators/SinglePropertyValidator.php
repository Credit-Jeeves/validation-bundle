<?php

namespace RentJeeves\DataBundle\Validators;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Validator;
use RentJeeves\DataBundle\Entity\Property;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Validator("single_property_validator")
 */
class SinglePropertyValidator extends ConstraintValidator
{
    protected $em;

    /**
     * @InjectParams({
     *     "em" = @Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function validate($value, Constraint $constraint)
    {
        $formData = $this->context->getRoot()->getData();
        if (isset($formData['property'])) {
            $propertyId = $formData['property'];

            /** @var Property $property */
            $property = $this->em->getRepository('RjDataBundle:Property')->find($propertyId);
            if ($property && ($property->hasUnits() || $property->hasGroups() || ($property->getIsSingle() !== $value && $property->getIsSingle() !== null))) {

            }
        }
    }
}
