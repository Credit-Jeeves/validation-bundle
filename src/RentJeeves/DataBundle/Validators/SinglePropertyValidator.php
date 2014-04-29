<?php

namespace RentJeeves\DataBundle\Validators;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Validator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Validator("tenant_email_validator")
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
        $user = $this->em->getRepository('DataBundle:User')->findOneBy(
            array(
                'email'     => $value,
            )
        );
        if ($user) {
            return true;
        }
    }
}
