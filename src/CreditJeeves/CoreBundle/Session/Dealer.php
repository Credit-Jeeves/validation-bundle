<?php
namespace CreditJeeves\CoreBundle\Session;

use JMS\DiExtraBundle\Annotation\Service;
use CreditJeeves\DataBundle\Entity\Dealer as UserEntity;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

/**
 * @Service("core.session.dealer")
 */
class Dealer extends User
{
    public function setUser(UserEntity $User)
    {
    }
}
