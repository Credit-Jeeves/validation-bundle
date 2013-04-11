<?php
namespace CreditJeeves\ApplicantBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use CreditJeeves\ApplicantBundle\Entity\User;

class EmailToUserTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Transforms an object (Group) to a string (sCode).
     *
     * @param  Group|null $Group
     * @return string
     */
    public function transform($User)
    {
        if (!$User) {
            return null;
        }
        return $User->getEmail();
    }

    /**
     * Transforms a string (sCode) to an object (Group).
     *
     * @param  string $sCode
     *
     * @return Group|null
     *
     * @throws TransformationFailedException if object (Group) is not found.
     */
    public function reverseTransform($sEmail)
    {
        if (null === $sEmail) {
            return "";
        }
        $User = $this->om->getRepository('DataBundle:User')->findOneByEmail($sEmail);
        if (null === $User) {
            throw new TransformationFailedException(sprintf('User with email "%s" does not exist!', $sEmail));
        }
        return $User;
    }
}
