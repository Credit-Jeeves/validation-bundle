<?php

namespace RentJeeves\DataBundle\Validators;

use CreditJeeves\DataBundle\Entity\User;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use JMS\DiExtraBundle\Annotation\Validator;
use CreditJeeves\DataBundle\Enum\UserType;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Validator("tenant_email_validator")
 */
class TenantEmailValidator extends ConstraintValidator
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

        if (!($user instanceof Tenant)) {
            $this->context->addViolation($this->translate($constraint->messageExistEmail));
            return false;
        }
        /**
         * @var $user Tenant
         */
        if (!$user->getIsActive() && $user->getInviteCode() && $user->getContracts()->count() > 0) {
            $this->context->addViolation(
                $this->translate(
                    $constraint->messageGetInvite,
                    array(
                        "%%LINK%%" => $this->router->generate(
                            'tenant_invite_resend',
                            array(
                                'userId' => $user->getId()
                            ),
                            true
                        )
                    )
                )
            );
            return false;
        }

        $this->context->addViolation($this->translate($constraint->messageExistEmail));
        return false;
    }

    protected function translate($message, $params = array())
    {
        return $this->i18n->trans(
            $message,
            $params
        );
    }
}
