<?php
namespace CreditJeeves\CoreBundle\Session;

use JMS\DiExtraBundle\Annotation\Service;
use CreditJeeves\DataBundle\Entity\User as UserEntity;

/**
 * @Service("core.session.tenant")
 */
class Tenant extends User
{
    /**
     * @param UserEntity $User
     */
    public function setUser(UserEntity $User)
    {
        $this->saveToSession(self::USER_TENANT);
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\User
     */
    public function getUser()
    {
        $data = $this->getFromSession(self::USER_TENANT);
        if (isset($data['user_id'])) {
            return $this->findUser($data['user_id']);
        }
        return new UserEntity();
    }
}
