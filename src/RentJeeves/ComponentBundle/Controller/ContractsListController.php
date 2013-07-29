<?php
namespace RentJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ContractsListController extends Controller
{
    /**
     * @Template("RjComponentBundle:ContractsList:landlord.html.twig")
     * @return multitype:
     */
    public function indexAction(\CreditJeeves\DataBundle\Entity\Group $Group)
    {
        return array(
        );
    }

    /**
     * @Template("RjComponentBundle:ContractsList:actions.html.twig")
     * @return multitype:
     */
    public function requiredAction(\CreditJeeves\DataBundle\Entity\Group $Group)
    {
        return array(
        );
    }

    /**
     * @Template()
     * @return multitype:
     */
    public function tenantNewAction($status)
    {
        return array(
            'contracts' => $this->getUser()->getContracts(),
            'status'    => $status,
        );
    }
    /**
     * @Template()
     * @return multitype:
     */
    public function tenantAction()
    {
        return array(
            'contracts' => $this->getUser()->getContracts(),
        );
    }
}
