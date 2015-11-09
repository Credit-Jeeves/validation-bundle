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
 * @Service("user.details.type.processor")
 */
class UserDetailsTypeProcessor
{
    protected $em;

    protected $isNewAddress = false;

    protected $address = null;

    /**
     * @InjectParams({
     *     "em" = @Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function save($userType, $user)
    {
        /** @var Address $address */
        $this->em->getRepository('DataBundle:MailingAddress')->resetDefaults($user->getId());
        /** @var Address $addressChoice */
        /** @var Address $newAddress */
        if ($addressChoice = $userType->get('address_choice')->getData()) {
            $this->address = $addressChoice;
        } elseif ($newAddress = $userType->get('new_address')->getData()) {
            $this->address = $newAddress;
            $this->address->setUser($user);
            $this->isNewAddress = true;
        }
        $this->address->setIsDefault(true);
        $data = $userType->getData();
        $this->em->persist($this->address);
        $this->em->persist($data);
        $this->em->flush();
    }

    public function getIsNewAddress()
    {
        return $this->isNewAddress;
    }

    public function getAddress()
    {
        return $this->address;
    }
}
