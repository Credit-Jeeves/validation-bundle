<?php
namespace RentJeeves\CoreBundle\Controller;

use CreditJeeves\CoreBundle\Controller\BaseController;
use CreditJeeves\DataBundle\Entity\Group;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Landlord;

class LandlordController extends BaseController
{
    /**
     * @return Landlord
     */
    public function getUser()
    {
        if ($user = parent::getUser()) {
            return $this->get('core.session.landlord')->getUser();
        }
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|Group[]
     */
    public function getGroups()
    {
        $user = $this->getUser();

        return $user->getGroups();
    }

    /**
     * @return Group|null
     */
    public function getCurrentGroup()
    {
        return $this->get('core.session.landlord')->getGroup();
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->get('logger');
    }
}
