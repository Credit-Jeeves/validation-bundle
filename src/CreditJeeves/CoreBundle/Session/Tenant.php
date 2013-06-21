<?php
namespace CreditJeeves\CoreBundle\Session;

use CreditJeeves\DataBundle\Enum\UserType;
use JMS\DiExtraBundle\Annotation\Service;
use CreditJeeves\DataBundle\Entity\Tenant as UserEntity;

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
        $this->data['user_id'] = $User->getId();
        $this->saveToSession(UserType::TETNANT);
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\User
     */
    public function getUser()
    {
        $data = $this->getFromSession(UserType::TETNANT);
        if (isset($data['user_id'])) {
            return $this->findUser($data['user_id']);
        }
        return new UserEntity();
    }
}
