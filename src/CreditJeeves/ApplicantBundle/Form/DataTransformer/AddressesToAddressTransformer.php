<?php
namespace CreditJeeves\ApplicantBundle\Form\DataTransformer;

use CreditJeeves\DataBundle\Entity\Address;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class AddressesToAddressTransformer implements DataTransformerInterface
{
    /**
     * Transforms a value from the original representation to a transformed representation.
     *
     * @param ArrayCollection $addresses
     *
     * @return Address
     */
    public function transform($addresses)
    {
        $address = null;
        if (null != $addresses) {
            $address = $addresses->last();
        }
        if (null == $address) {
            $address =  new Address();
        }
        return $address;
    }

    /**
     * Transforms a value from the transformed representation to its original
     * representation.
     *
     * @param Address $address
     *
     * @return ArrayCollection
     */
    public function reverseTransform($address)
    {
        return new ArrayCollection(array($address));
    }
}
