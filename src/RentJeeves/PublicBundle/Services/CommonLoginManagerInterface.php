<?php

namespace RentJeeves\PublicBundle\Service;

use CreditJeeves\DataBundle\Entity\User;

interface CommonLoginManagerInterface
{
    public function login(User $user);
}
