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
class Admin extends User
{
    public function setUser(cjUser $User)
    {
        $attribute = new AttributeBag();
        $attribute->setName(self::BAG_DEALER);
        $this->session->registerBag($attribute);
        $attribute = new AttributeBag();
        $attribute->setName(self::BAG_ADMIN);
        $this->session->registerBag($attribute);
    }
}