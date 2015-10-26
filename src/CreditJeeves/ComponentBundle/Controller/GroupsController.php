<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class GroupsController extends Controller
{
    /**
     * @Template
     *
     * @return array
     */
    public function indexAction()
    {
        $User = $this->get('core.session.landlord')->getUser();
        $groups = $this->get('core.session.landlord')->getGroups($User);
        $result = array();
        foreach ($groups as $group) {
            $aItem = array();
            $aItem['name'] = $group->getName();
            $aItem['id'] = $group->getId();
            $result[] = $aItem;
        }

        return array(
            'aGroups' => $result,
            'nCurrent' => $this->get('core.session.landlord')->getGroupId()
            );
    }
}
