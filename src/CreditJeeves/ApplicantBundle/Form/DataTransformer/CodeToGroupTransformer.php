<?php
namespace CreditJeeves\ApplicantBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use CreditJeeves\ApplicantBundle\Entity\Group;

class CodeToGroupTransformer implements DataTransformerInterface
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
    public function transform($Group)
    {
        if (!$Group) {
            return null;
        }
        return $Group->getCode();
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
    public function reverseTransform($sCode)
    {
        if (null === $sCode) {
            return "";
        }
        $Group = $this->om->getRepository('DataBundle:Group')->findOneByCode($sCode);
        if (null === $Group) {
            throw new TransformationFailedException(sprintf(
                    'Group with code "%s" does not exist!',
                    $sCode
            ));
        }
        return $Group;
    }
}