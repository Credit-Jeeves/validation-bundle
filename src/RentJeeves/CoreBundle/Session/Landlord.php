<?php
namespace RentJeeves\CoreBundle\Session;

use CreditJeeves\CoreBundle\Session\User;
use CreditJeeves\DataBundle\Enum\UserType;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\DataBundle\Entity\Landlord as UserEntity;
use CreditJeeves\DataBundle\Entity\Group;

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
            if ($user = $this->findUser($data['user_id'])) {
                return $user;
            }
        }

        return new UserEntity();
    }

    public function prepareLandlord(UserEntity $User)
    {
        $Lead = $this->getActiveGroup($User);
        $this->data['user_id'] = $User->getId();
        $this->data['group_id'] = $Lead->getId();
    }

    /**
     *
     * @param integer $nLeadId
     */
    public function setGroupId($nGroupId)
    {
        $this->data = $this->getFromSession(UserType::LANDLORD);
        $this->data['group_id'] = $nGroupId;
        $this->saveToSession(UserType::LANDLORD);
    }

    /**
     * @return integer
     */
    public function getGroupId()
    {
        $data = $this->getFromSession(UserType::LANDLORD);

        return isset($data['group_id']) ? $data['group_id'] : null;
    }

    /**
     * @return Group|null
     */
    public function getGroup()
    {
        if ($this->getGroupId()) {
            return $this->em->getRepository('DataBundle:Group')->find($this->getGroupId());
        } else {
            return null;
        }
    }

    public function getActiveGroup($User)
    {
        if ($isAdmin = $User->getIsSuperAdmin()) {
            $nGroups = $User->getHolding()->getGroups()->count();
            if ($nGroups > 0) {
                return $User->getHolding()->getGroups()->first();
            } else {
                return new Group();
            }
        } else {
            $nGroups = $User->getAgentGroups()->count();
            if ($nGroups > 0) {
                return $User->getAgentGroups()->first();
            } else {
                return new Group();
            }
        }
    }

    public function getGroups($User)
    {
        if ($isAdmin = $User->getIsSuperAdmin()) {
            return $User->getHolding()->getGroups();
        } else {
            return $User->getAgentGroups();
        }
    }
}
