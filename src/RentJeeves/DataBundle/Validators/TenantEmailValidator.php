<?php

namespace RentJeeves\DataBundle\Validators;

use CreditJeeves\DataBundle\Entity\User;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use JMS\DiExtraBundle\Annotation\Validator;
use CreditJeeves\DataBundle\Enum\UserType;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * This validator add error into flash with key tenant_email_error
 * disable escape for this errors
 *
 * @Validator("tenant_email_validator")
 */
class TenantEmailValidator extends ConstraintValidator
{
    const ERROR_NAME = 'tenant_email_error';

    protected $em;

    protected $router;

    protected $i18n;

    protected $session;

    /**
     * @InjectParams({
     *     "em"      = @Inject("doctrine.orm.entity_manager"),
     *     "router"  = @Inject("router"),
     *     "i18n"    = @Inject("translator"),
     *     "session" = @Inject("session"),
     * })
     */
    public function __construct(EntityManager $em, $router, $i18n, Session $session)
    {
        $this->em = $em;
        $this->router = $router;
        $this->i18n = $i18n;
        $this->session = $session;
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
            $this->addError($this->translate($constraint->messageExistEmail));
            return false;
        }
        /**
         * @var $user Tenant
         */
        if (!$user->getIsActive() && $user->getInviteCode() && $user->getContracts()->count() > 0) {
            $this->addError(
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

        $this->addError($this->translate($constraint->messageExistEmail));
        return false;
    }

    protected function translate($message, $params = array())
    {
        return $this->i18n->trans(
            $message,
            $params
        );
    }

    protected function addError($message)
    {
        $this->session->getFlashBag()->set(static::ERROR_NAME, $message);
        $this->context->addViolation('');
    }
}
