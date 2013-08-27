<?php
namespace RentJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class PropertiesListController extends Controller
{
    /**
     * @Template()
     * @param \CreditJeeves\DataBundle\Entity\Group $Group
     * @return mixed
     */
    public function indexAction(\CreditJeeves\DataBundle\Entity\Group $Group)
    {
        return array(
            'sGroup' => $Group->getName(),
        );
    }
}
