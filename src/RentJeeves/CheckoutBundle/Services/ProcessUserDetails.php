<?php

namespace RentJeeves\CheckoutBundle\Services;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Doctrine\ORM\EntityManager;
use SoftDeleteable\Fixture\Entity\Address;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("process.user.details.type")
 */
class ProcessUserDetails
{
    protected $em;

    /**
     * @InjectParams({
     *     "em"     = @Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function process($userType, $user)
    {
        /** @var Address $address */
        $address = null;
        $isNewAddress = false;
        $this->em->getRepository('DataBundle:Address')->resetDefaults($user->getId());
        /** @var Address $addressChose */
        /** @var Address $newAddress */
        if ($addressChose = $userType->get('address_choice')->getData()) {
            $address = $addressChose;
        } elseif ($newAddress = $userType->get('new_address')->getData()) {
            $address = $newAddress;
            $address->setUser($user);
            $isNewAddress = true;
        }
        $address->setIsDefault(1);
        $data = $userType->getData();
        $this->em->persist($address);
        $this->em->persist($data);
        $this->em->flush();

        return array(
            $isNewAddress,
            $address
        );
    }
}
