<?php
namespace CreditJeeves\CoreBundle\Session;

use CreditJeeves\DataBundle\Enum\UserType;
use JMS\DiExtraBundle\Annotation\Service;
use CreditJeeves\DataBundle\Entity\Landlord as UserEntity;

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
        $this->prepareLandlord($User);
        $this->saveToSession(UserType::LANDLORD);
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\User
     */
    public function getUser()
    {
        $data = $this->getFromSession(UserType::LANDLORD);
        if (isset($data['user_id'])) {
            return $this->findUser($data['user_id']);
        }
        return new UserEntity();
    }

    public function prepareLandlord(UserEntity $User)
    {
        $Lead = $User->getActiveGroup();
        $this->data['user_id'] = $User->getId();
        $this->data['group_id'] = $Lead->getId();
    }
    
    /**
     * @return integer
     */
    public function getGroupId()
    {
        $data = $this->getFromSession(UserType::LANDLORD);
    
        return isset($data['group_id']) ? $data['group_id'] : null;
    }
    public function getGroup()
    {
        return $this->em->getRepository('DataBundle:Group')->find($this->getGroupId());
    }
}
