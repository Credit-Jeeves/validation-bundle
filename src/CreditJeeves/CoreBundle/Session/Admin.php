<?php
namespace CreditJeeves\CoreBundle\Session;

use JMS\DiExtraBundle\Annotation\Service;
use CreditJeeves\DataBundle\Entity\User as cjUser;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

/**
 * @Service("core.session.admin")
 */
class Admin extends User
{
    public function setUser(cjUser $User)
    {
        $this->prepareAdmin($User);
        $this->saveToSession(self::USER_ADMIN);
    }

    public function prepareAdmin(cjUser $User)
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
