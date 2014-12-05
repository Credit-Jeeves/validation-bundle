<?php

namespace RentJeeves\ApiBundle\Validator;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\TranslatorInterface as Translator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use JMS\DiExtraBundle\Annotation\Validator;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;

/**
 * @Validator("api_tenant_email_validator")
 */
class ApiTenantEmailValidator extends ConstraintValidator
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
        $user = $this->em->getRepository('DataBundle:User')->findOneBy(['email' => $value]);

        if (!$user) {
            return true;
        }

        $this->context->addViolation($constraint->message);
    }
}
