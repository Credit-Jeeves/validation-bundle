<?php
namespace CreditJeeves\UserBundle\Security\Voter;

use CreditJeeves\DataBundle\Entity\Address;
use CreditJeeves\DataBundle\Entity\User;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Service("security.access.address")
 * @Tag("security.voter")
 */
class AddressVoter implements VoterInterface
{
    const DELETE = 'delete';

    /**
     * @param string $attribute
     *
     * @return bool
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, [
            self::DELETE,
        ]);
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        $supportedClass = 'CreditJeeves\DataBundle\Entity\Address';

        return $supportedClass === $class || is_subclass_of($class, $supportedClass);
    }

    /**
     * @param TokenInterface $token
     * @param null|\CreditJeeves\DataBundle\Entity\Address $address
     * @param array $attributes
     *
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    public function vote(TokenInterface $token, $address, array $attributes)
    {
        // check if class of this object is supported by this voter
        if (false === $this->supportsClass(get_class($address))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // check if the voter is used correct, only allow one attribute
        // this isn't a requirement, it's just one easy way for you to
        // design your voter
        if (1 !== count($attributes)) {
            throw new \InvalidArgumentException(
                'Only one attribute is allowed for DELETE'
            );
        }

        $attribute = $attributes[0];

        if (false === $this->supportsAttribute($attribute)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        /** @var User $user */
        $user = $token->getUser();

        if (false === $user instanceof UserInterface) {
            return VoterInterface::ACCESS_DENIED;
        }

        switch ($attribute) {
            case self::DELETE:

                if (
                    $address->getUser() === $user &&
                    $address->getIsDefault() === false &&
                    $this->hasActivePaymentAccount($address) === false
                ) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    /**
     * @param Address $address
     *
     * @return bool
     */
    private function hasActivePaymentAccount(Address $address)
    {
        foreach($address->getPaymentAccounts() as $paymentAccount){
            if($paymentAccount->getDeletedAt() === null){
                return true;
            }
        }

        return false;
    }
}
