<?php
namespace CreditJeeves\CoreBundle\Session;

use JMS\DiExtraBundle\Annotation\Service;
use CreditJeeves\DataBundle\Entity\User as cjUser;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

/**
 * 
 * @Service("core.session.applicant")
 *
 */
class Applicant extends User
{
    public function setUser(cjUser $User)
    {
        $attribute = new AttributeBag();
        $attribute->setName(self::BAG_APPLICANT);
        $this->session->registerBag($attribute);
        $Lead = $User->getActiveLead();
        $this->session->set('nLeadId', $Lead->getId());
    }
        
    public function getLeadId()
    {
        
        return  $this->session->get('nLeadId', null);
    }
    
//     public function setUser(cjUser $User)
//     {
        
//         $this->session->setName('applicant');
//         $Lead = $User->getActiveLead();
//         $this->session->set('nLeadId', $Lead->getId());
        
//     }
}