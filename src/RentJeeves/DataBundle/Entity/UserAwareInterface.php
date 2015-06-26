<?php

namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Entity\Address;

interface UserAwareInterface
{
    /**
     * @return User
     */
    public function getUser();

    /**
     * @return Address
     */
    public function getAddress();

    /**
     * @param User|null $user
     */
    public function setUser(User $user = null);

    /**
     * @param Address|null $address
     */
    public function setAddress(Address $address = null);
}
