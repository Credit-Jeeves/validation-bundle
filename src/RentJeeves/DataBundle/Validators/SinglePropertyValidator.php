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

    protected $supportEmail;

    /**
     * @InjectParams({
     *     "em" = @Inject("doctrine.orm.entity_manager"),
     *     "supportEmail" = @Inject("%support_email%")
     * })
     */
    public function __construct(EntityManager $em, $supportEmail)
    {
        $this->em = $em;
        $this->supportEmail = $supportEmail;
    }

    public function validate($value, Constraint $constraint)
    {
        $formData = $this->context->getRoot()->getData();
        if ($propertyId = $formData['property']['propertyId']) {

            /** @var Property $property */
            $property = $this->em->getRepository('RjDataBundle:Property')->find($propertyId);
            if ($property &&
                ($property->hasUnits() || $property->hasGroups() ||
                    ($property->getPropertyAddress()->isSingle() !== $value &&
                        $property->getPropertyAddress()->isSingle() !== null)
                )
            ) {
                $this->context->addViolation(
                    $constraint->commonMessage,
                    array('%SUPPORT_EMAIL%' => $this->supportEmail)
                );

                return false;
            }

            if ($value == false && count($formData['property']['units']) == 0) {
                $this->context->addViolation($constraint->emptyUnitsMessage);
            }
        }
    }
}
