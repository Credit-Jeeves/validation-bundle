<?php
namespace CreditJeeves\CoreBundle\Session;

use JMS\DiExtraBundle\Annotation\Service;
use CreditJeeves\DataBundle\Entity\User as UserEntity;

/**
 * @Service("core.session.landlord")
 */
class Landlord extends User
{
    /**
     * @param UserEntity $User
     */
    public function setUser(UserEntity $User)
    {
        $this->saveToSession(self::USER_LANDLORD);
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\User
     */
    public function getUser()
    {
        $data = $this->getFromSession(self::LANDLORD);
        if (isset($data['user_id'])) {
            return $this->findUser($data['user_id']);
        }
        return new UserEntity();
    }
}
