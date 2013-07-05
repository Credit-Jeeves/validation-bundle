<?php
namespace CreditJeeves\CoreBundle\Session;

use JMS\DiExtraBundle\Annotation\Service;
use CreditJeeves\DataBundle\Entity\Dealer as UserEntity;
//use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use CreditJeeves\DataBundle\Enum\UserType;

/**
 * @Service("core.session.dealer")
 */
class Dealer extends User
{
    /**
     * @param UserEntity $User
     */
    public function setUser(UserEntity $User)
    {
        $this->saveToSession(UserType::DEALER);
    }
    
    /**
     * @return \CreditJeeves\DataBundle\Entity\User
     */
    public function getUser()
    {
        $data = $this->getFromSession(UserType::DEALER);
        if (isset($data['user_id'])) {
            return $this->findUser($data['user_id']);
        }
        return new UserEntity();
    }
}
