<?php

namespace RentJeeves\DataBundle\Validators;

use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\UserCulture;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Invite;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use JMS\DiExtraBundle\Annotation\Validator;
use CreditJeeves\DataBundle\Enum\UserType;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * This validator add error into flash with key landlord_email_error
 * disable escape for this errors
 *
 * @Validator("landlord_email_validator")
 */
class LandlordEmailValidator extends TenantEmailValidator
{
    const ERROR_NAME = 'landlord_email_error';

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

        if ($user->getType() === UserType::LANDLORD && !$user->getIsActive()) {
            $this->addError(
                $this->translate(
                    $constraint->messageGetInvite,
                    array(
                        "%%LINK%%" => $this->router->generate(
                            'landlord_invite_resend',
                            array(
                                'landlordId' => $user->getId()
                            ),
                            true
                        )
                    )
                )
            );
            return false;
        }

        $this->addError($this->translate($constraint->messageExistEmail));
        return true;
    }
}
