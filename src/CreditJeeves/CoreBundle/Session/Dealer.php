<?php
namespace CreditJeeves\CoreBundle\Session;

use JMS\DiExtraBundle\Annotation\Service;
use CreditJeeves\DataBundle\Entity\User as cjUser;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

/**
 * 
 * @Service("core.session.dealer")
 *
 */
class Dealer extends User
{
    public function setUser(cjUser $User)
    {
        $attribute = new AttributeBag();
        $attribute->setName('dealer');
        $this->session->registerBag($attribute);
    }
}