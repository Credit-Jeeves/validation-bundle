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

    protected $translator;

    /**
     * @InjectParams({
     *     "em" = @Inject("doctrine.orm.entity_manager"),
     *     "translator" = @Inject("translator"),
     * })
     */
    public function __construct(EntityManager $em, Translator $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    public function validate($value, Constraint $constraint)
    {
        $user = $this->em->getRepository('DataBundle:User')->findOneBy(['email' => $value]);

        if (!$user) {
            return true;
        }

        $this->context->addViolation($this->translator->trans($constraint->message));
    }
}
