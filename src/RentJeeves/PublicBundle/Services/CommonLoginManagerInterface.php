<?php

namespace RentJeeves\PublicBundle\Services;

use CreditJeeves\DataBundle\Entity\User;

interface CommonLoginManagerInterface
{
    public function login(User $user);
}
