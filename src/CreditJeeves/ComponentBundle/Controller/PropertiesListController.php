<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class PropertiesListController extends Controller
{
    /**
     * @Template()
     * @param \CreditJeeves\DataBundle\Entity\Group $Group
     * @return multitype:
     */
    public function indexAction(\CreditJeeves\DataBundle\Entity\Group $Group)
    {
        $repo = $this->get('doctrine.orm.default_entity_manager')->getRepository('RjDataBundle:Property');
        $properties = $repo->findAll();
        $result = array();
        foreach ($properties as $property) {
            $result[] = $property->getItem();
        }
        $jsonProperties = json_encode($result);
        return array(
            'sGroup' => $Group->getName(),
            'jsonProperties' => $jsonProperties,
        );
    }
}
