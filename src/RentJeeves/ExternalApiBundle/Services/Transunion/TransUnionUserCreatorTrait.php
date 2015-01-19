<?php
namespace RentJeeves\ExternalApiBundle\Services\Transunion;

use RentJeeves\DataBundle\Entity\Tenant;
use RentTrack\TransUnionBundle\CCS\Model\TransUnionUser;

trait TransUnionUserCreator
{
    public function getTransUnionUser(Tenant $user)
    {
        $address = $user->getDefaultAddress();

        $tuUser = new TransUnionUser();
        $tuUser
            ->setClientId($user->getId())
            ->setFirstName($user->getFirstName())
            ->setLastName($user->getLastName())
            ->setDateOfBirth($user->getDateOfBirth()->format('Y-m-d'))
            ->setSsn($user->getSsn())
            ->setStreet($address->getAddress())
            ->setCity($address->getCity())
            ->setState($address->getArea())
            ->setZipCode($address->getZip());

        return $tuUser;
    }
}
