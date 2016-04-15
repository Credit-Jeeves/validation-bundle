<?php

namespace CreditJeeves\UserBundle\Helper;

use CreditJeeves\DataBundle\Entity\User;

class UserNormalizerForPidkiq
{
    /**
     * @param User $user
     *
     * @return array
     */
    public static function normalizeUserForPidkiq(User $user)
    {
        $return = [
            'id' => $user->getId(),
            'first_name' => $user->getFirstName(),
            'middle_initial'=> $user->getMiddleInitial(),
            'last_name'=> $user->getLastName(),
            'ssn'=> $user->getSsn(),
            'is_verified' => $user->getIsVerified(),
            'unit' => '',
            'number' => '',
            'street' => '',
            'city' => '',
            'zip' => '',
            'country' => '',
        ];

        if ($address = $user->getDefaultAddress()) {
            $return['unit'] = $address->getUnit();
            $return['number'] = $address->getNumber();
            $return['street'] = $address->getStreet();
            $return['city'] = $address->getCity();
            $return['zip'] = $address->getZip();
            $return['country'] = $address->getCountry();
        }

        return $return;
    }
}
