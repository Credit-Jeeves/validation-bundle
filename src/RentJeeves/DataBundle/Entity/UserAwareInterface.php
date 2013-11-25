<?php

namespace RentJeeves\DataBundle\Entity;

interface UserAwareInterface
{
    public function getUser();
    public function setUser(\RentJeeves\DataBundle\Entity\Tenant $user = null);
}
