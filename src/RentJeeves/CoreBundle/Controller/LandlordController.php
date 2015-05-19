<?php
namespace RentJeeves\CoreBundle\Controller;

use CreditJeeves\CoreBundle\Controller\BaseController;
use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\Landlord;
use Monolog\Logger;

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

    public function getGroups()
    {
        $user = $this->getUser();

        return $user->getGroups();
    }

    /**
     * @return Group
     */
    public function getCurrentGroup()
    {
        return $this->get('core.session.landlord')->getGroup();
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->get('logger');
    }
}
