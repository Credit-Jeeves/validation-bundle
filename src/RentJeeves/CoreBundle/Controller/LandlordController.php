<?php
namespace RentJeeves\CoreBundle\Controller;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\Landlord;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LandlordController extends Controller
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
}
