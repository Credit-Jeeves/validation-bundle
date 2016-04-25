<?php
namespace RentJeeves\CoreBundle\UserManagement;

use CreditJeeves\DataBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Exception\UserCreatorException;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator;

/**
 * Service`s name 'renttrack.user_creator'
 */
class UserCreator
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @param EntityManagerInterface $em
     * @param LoggerInterface        $logger
     * @param Validator              $validator
     */
    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, Validator $validator)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->validator = $validator;
    }

    /**
     * @param string      $firstName
     * @param string      $lastName
     * @param string|null $email
     *
     * @return Tenant
     */
    public function createTenant($firstName, $lastName, $email = null)
    {
        $this->logger->debug(
            'Try to create new Tenant.',
            ['firstName' => $firstName, 'lastName' => $lastName, 'email' => $email]
        );

        if (null === $email) {
            $tenant = $this->createTenantWithoutEmail($firstName, $lastName);
        } else {
            $tenant = $this->createTenantWithEmail($firstName, $lastName, $email);
        }

        $this->logger->debug(
            'Try to create new Tenant.',
            ['firstName' => $firstName, 'lastName' => $lastName, 'email' => $email]
        );

        return $tenant;
    }

    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     *
     * @throws UserCreatorException if user with input email already exists
     *
     * @return Tenant
     */
    protected function createTenantWithEmail($firstName, $lastName, $email)
    {
        $email = strtolower($email);
        if (null !== $this->getUserRepository()->findOneBy(['emailCanonical' => $email])) {
            throw new UserCreatorException(
                sprintf('User with email "%s" already exist.', $email)
            );
        }

        $newTenant = new Tenant();
        $newTenant->setFirstName($firstName);
        $newTenant->setLastName($lastName);
        $newTenant->setUsername($email);
        $newTenant->setEmailField($email);
        $newTenant->setUsernameCanonical($email);
        $newTenant->setEmailCanonical($email);
        $newTenant->setPassword($this->generatePassword());
        $newTenant->setEmailNotification(true);
        $newTenant->setOfferNotification(true);

        $this->validate($newTenant);

        $this->em->persist($newTenant);
        $this->em->flush();

        return $newTenant;
    }

    /**
     * @param string $firstName
     * @param string $lastName
     *
     * @throws UserCreatorException if input data is empty
     *
     * @return Tenant
     */
    protected function createTenantWithoutEmail($firstName, $lastName)
    {
        if (true === empty($firstName) || true === empty($lastName)) {
            throw new UserCreatorException(
                'Fields "firstName" and "lastName" are required for creating Tenant without email.'
            );
        }

        $userName = $this->generateUserName($firstName, $lastName);

        $newTenant = new Tenant();
        $newTenant->setFirstName($firstName);
        $newTenant->setLastName($lastName);
        $newTenant->setUsername($userName);
        $newTenant->setUsernameCanonical($userName);
        $newTenant->setPassword($this->generatePassword());
        $newTenant->setEmailNotification(false);
        $newTenant->setOfferNotification(false);

        $this->validate($newTenant);

        $this->em->persist($newTenant);
        $this->em->flush();

        return $newTenant;
    }

    /**
     * @param string $firstName
     * @param string $lastName
     *
     * @return string
     */
    protected function generateUserName($firstName, $lastName)
    {
        $userName = strtolower($firstName . $lastName);
        if (null !== $lastExistUserName = $this->getUserRepository()->getLastUserNameByPartOfUserName($userName)) {
            $digits = substr($lastExistUserName, strlen($userName));
            if (true === empty($digits)) {
                $userName .= 1;
            } else {
                $userName .= (int) $digits + 1;
            }
        }

        return $userName;
    }

    /**
     * @return string
     */
    protected function generatePassword()
    {
        return md5(uniqid());
    }

    /**
     * @param $user
     *
     * @throws UserCreatorException
     */
    protected function validate(User $user)
    {
        $errors = [];
        /** @var ConstraintViolation $error */
        $validatorErrors = $this->validator->validate($user, ['userCreationManager']);
        if ($validatorErrors->count() > 0) {
            foreach ($validatorErrors as $error) {
                $errors[] = sprintf(
                    '%s : %s',
                    $error->getPropertyPath(),
                    $error->getMessage()
                );
            }
        }

        if (false === empty($errors)) {
            $this->logger->debug(
                $message = sprintf(
                    'User(%s) is not valid : %s',
                    get_class($user),
                    implode(', ', $errors)
                )
            );
            throw new UserCreatorException($message);
        }
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\UserRepository
     */
    protected function getUserRepository()
    {
        return $this->em->getRepository('DataBundle:User');
    }
}
