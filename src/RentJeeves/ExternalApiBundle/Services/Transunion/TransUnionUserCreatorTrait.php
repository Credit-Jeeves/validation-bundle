<?php
namespace RentJeeves\ExternalApiBundle\Services\Transunion;

use CreditJeeves\DataBundle\Entity\User;
use RentTrack\TransUnionBundle\CCS\Model\TransUnionUser;

trait TransUnionUserCreatorTrait
{
    public function getTransUnionUser(User $user)
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
