<?php
namespace RentJeeves\CoreBundle\UserManagement;

use CreditJeeves\DataBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Exception\UserCreationManagerException;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator;

class UserCreationManager
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
     * @param string $firstName
     * @param string $lastName
     *
     * @throw \InvalidArgumentException if input data is empty
     *
     * @return Tenant
     */
    public function createTenantWithoutEmail($firstName, $lastName)
    {
        if (true === empty($firstName) || true === empty($lastName)) {
            throw new \InvalidArgumentException('Fields "firstName" and "lastName" are required.');
        }

        $userName = $this->generateUserName($firstName, $lastName);

        $newTenant = new Tenant();
        $newTenant->setFirstName($firstName);
        $newTenant->setLastName($lastName);
        $newTenant->setUsername($userName);
        $newTenant->setUsernameCanonical($userName);
        $newTenant->setPassword($this->generatePassword());

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
        if (null !== $user = $this->getUserRepository()->findLastByPartOfUserName($userName)) {
            $digits = substr($user->getUsernameCanonical(), strlen($userName));
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
        return md5(md5(1));
    }

    /**
     * @param $user
     *
     * @throws UserCreationManagerException
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
            throw new UserCreationManagerException($message);
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
