<?php

namespace RentJeeves\LandlordBundle\Validator;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Validator;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Entity\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @Validator("user_email_exist")
 */
class EmailExistValidator extends ConstraintValidator
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var string
     */
    protected $supportEmail;

    /**
     * @param EntityManager $em
     * @param string $supportEmail
     *
     * @InjectParams({
     *     "em" = @Inject("doctrine.orm.entity_manager"),
     *     "supportEmail" = @Inject("%support_email%")
     * })
     */
    public function __construct(EntityManager $em, $supportEmail)
    {
        $this->userRepository = $em->getRepository('DataBundle:User');
        $this->supportEmail = $supportEmail;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $value = (string) $value;

        /** @var User $user */
        if ($user = $this->userRepository->findOneByEmail($value)) {

            $this->context->addViolation(
                $constraint->messageExist,
                array_merge(
                    [
                        '%email%' => $this->formatValue($value),
                        '%user_fullname%' => $user->getFullName(),
                        '%support_email%' => $this->supportEmail,
                    ],
                    $constraint->messageExistParams
                )
            );
        }
    }
}
