<?php

namespace RentJeeves\DataBundle\Entity;

interface UserAwareInterface
{
    public function getUser();
    public function getAddress();
    public function setUser(\RentJeeves\DataBundle\Entity\Tenant $user = null);
    public function setAddress(\CreditJeeves\DataBundle\Entity\Address $address = null);
}
