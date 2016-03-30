<?php
namespace CreditJeeves\CoreBundle\Session;

use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Enum\UserType;
use JMS\DiExtraBundle\Annotation\Service;
use CreditJeeves\DataBundle\Entity\Applicant as UserEntity;

/**
 * @Service("core.session.applicant")
 */
class Applicant extends User
{
    /**
     * @param UserEntity $User
     */
    public function setUser(UserEntity $User)
    {
        $this->prepareApplicant($User);
        $this->saveToSession(UserType::APPLICANT);
    }

    /**
     *
     * @param UserEntity $User
     */
    public function prepareApplicant(UserEntity $User)
    {
        $Lead = $this->getActiveLead($User);
        $this->data['user_id'] = $User->getId();
        $this->data['lead_id'] = $Lead->getId();
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

    /**
     * @return integer
     */
    public function getLeadId()
    {
        $data = $this->getFromSession(UserType::APPLICANT);

        return isset($data['lead_id']) ? $data['lead_id'] : null;
    }

    /**
     *
     * @param integer $nLeadId
     */
    public function setLeadId($nLeadId)
    {
        $this->data = $this->getFromSession(UserType::APPLICANT);
        $this->data['lead_id'] = $nLeadId;
        $this->saveToSession(UserType::APPLICANT);
    }

    public function getLead()
    {
        return $this->em->getRepository('DataBundle:Lead')->find($this->getLeadId());
    }

    /**
     * @param \CreditJeeves\DataBundle\Entity\User $user
     *
     * @return Lead|mixed
     */
    private function getActiveLead(\CreditJeeves\DataBundle\Entity\User $user)
    {
        $nLeads = $user->getUserLeads()->count();
        if ($nLeads > 0) {
            return $user->getUserLeads()->last();
        } else {
            return new Lead();
        }
    }
}
