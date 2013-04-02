<?php
namespace CreditJeeves\CoreBundle\Session;

use JMS\DiExtraBundle\Annotation\Service;
use CreditJeeves\DataBundle\Entity\User as cjUser;

/**
 * 
 * @Service("core.session.applicant")
 *
 */
class Applicant extends User
{
    /**
     * 
     * @param cjUser $User
     */
    public function setUser(cjUser $User)
    {
        $this->prepareApplicant($User);
        $this->saveToSession(self::USER_APPLICANT);
    }

    /**
     * 
     * @param cjUser $User
     */
    public function prepareApplicant(cjUser $User)
    {
        $Lead = $User->getActiveLead();
        $this->data['user_id'] = $User->getId();
        $this->data['lead_id'] = $Lead->getId();
    }

    /**
     * @return CreditJeeves\DataBundle\Entity\User
     */
    public function getUser()
    {
        $data = $this->getFromSession(self::USER_APPLICANT);
        if (isset($data['user_id'])) {
            return $this->findUser($data['user_id']);
        }
    }

    /**
     * @return integer
     */
    public function getLeadId()
    {
        $data = $this->getFromSession(self::USER_APPLICANT);
        return  isset($data['lead_id']) ? $data['lead_id'] : null;
    }

    /**
     * 
     * @param integer $nLeadId
     */
    public function setLeadId($nLeadId)
    {
        $this->data = $this->getFromSession(self::USER_APPLICANT);
        $this->data['lead_id'] = $nLeadId;
        $this->saveToSession(self::USER_APPLICANT);
    }

    public function getLead()
    {
        return $this->em->getRepository('DataBundle:Lead')->find($this->getLeadId());
    }
}