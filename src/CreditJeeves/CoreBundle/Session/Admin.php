<?php
namespace CreditJeeves\CoreBundle\Session;

use JMS\DiExtraBundle\Annotation\Service;
use CreditJeeves\DataBundle\Entity\Admin as UserEntity;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

/**
 * @Service("core.session.admin")
 */
class Admin extends User
{
    public function setUser(UserEntity $User)
    {
        $this->prepareAdmin($User);
        $this->saveToSession(self::USER_ADMIN);
    }

    public function prepareAdmin(UserEntity $User)
    {
        $this->data['user_id'] = $User->getId();
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\User
     */
    public function getUser()
    {
        $data = $this->getFromSession(self::USER_ADMIN);
        if (isset($data['user_id'])) {
            return $this->findUser($data['user_id']);
        }
        return new UserEntity();
    }
}
