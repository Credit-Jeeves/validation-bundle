<?php
namespace RentJeeves\CoreBundle\Session;

use CreditJeeves\CoreBundle\Session\User;
use CreditJeeves\DataBundle\Enum\UserType;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\DataBundle\Entity\Tenant as UserEntity;

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
        $this->saveToSession(UserType::APPLICANT);
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\User
     */
    public function getUser()
    {
        $data = $this->getFromSession(UserType::APPLICANT);
        if (isset($data['user_id'])) {
            if ($user = $this->findUser($data['user_id'])) {
                return $user;
            }
        }
        return new UserEntity();
    }
}
