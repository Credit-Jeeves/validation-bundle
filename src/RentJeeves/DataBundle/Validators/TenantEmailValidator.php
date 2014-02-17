<?php

namespace RentJeeves\DataBundle\Validators;

use CreditJeeves\DataBundle\Entity\User;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use JMS\DiExtraBundle\Annotation\Validator;
use CreditJeeves\DataBundle\Enum\UserType;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Validator("tenant_email_validator")
 */
class TenantEmailValidator
{
    protected $em;

    protected $router;

    protected $i18n;

    /**
     * @InjectParams({
     *     "em"     = @Inject("doctrine.orm.entity_manager"),
     *     "router" = @Inject("router"),
     *     "i18n"   = @Inject("translator"),
     * })
     */
    public function __construct(EntityManager $em, $router, $i18n)
    {
        $this->em = $em;
        $this->router = $router;
        $this->i18n = $i18n;
    }

    public function validate($value, Constraint $constraint)
    {
        /**
         * @var $user User
         */
        $user = $this->em->getRepository('DataBundle:User')->findOneBy(
            array(
                'email'     => $value,
            )
        );

        if (empty($user)) {
            return true;
        }

        if (!$user->getIsActive() && $user->getType() === UserType::TENANT && $user->getInviteCode()) {
            $this->context->addViolation($this->translate($constraint->messageGetInvite));
            return false;
        }

        $this->context->addViolation($this->translate($constraint->messageExistEmail));
        return false;
    }

    //@todo setup correct link when move functional to service and will get link
    protected function translate($message)
    {
        $this->i18n->trans(
            $message,
            array(
                "%%LINK%%" => $this->router->generate('', array(), true)
            )
        );
    }
}
