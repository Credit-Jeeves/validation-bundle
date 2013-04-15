<?php
namespace CreditJeeves\ApplicantBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class SsnToPartsTransformer implements DataTransformerInterface
{
    /**
     * Transforms a value from the original representation to a transformed representation.
     * @param string $sSsn
     */
    public function transform($value)
    {
        if (null === $value) {
            return '';
        }
        //var_dump('transform',$value);
        return implode('', $value);
    }

    /**
     * Transforms a value from the transformed representation to its original
     * representation.
     * @param array $aSsn
     */
    public function reverseTransform($sSsn)
    {
        if (empty($sSsn)) {
            return null;
        }
        //var_dump('reverse', $sSsn);
        $aSsn = array(
                    'ssn1' => substr($sSsn, 0, 3),
                    'ssn2' => substr($sSsn, 2, 2),
                    'ssn3' => substr($sSsn, 4)
                );
        return $aSsn;
    }
}
