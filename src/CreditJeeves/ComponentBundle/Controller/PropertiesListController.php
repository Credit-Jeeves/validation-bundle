<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class PropertiesListController extends Controller
{
    /**
     * @Template()
     * @param \CreditJeeves\DataBundle\Entity\Lead $Lead
     * @return multitype:
     */
    public function indexAction(\CreditJeeves\DataBundle\Entity\Group $Group)
    {
        return array(
            'sGroup' => $Group->getName(),
            );
    }
}
