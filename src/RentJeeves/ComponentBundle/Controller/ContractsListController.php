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
    public function indexAction(\CreditJeeves\DataBundle\Entity\Group $Group, $form)
    {
        return array(
            'form'  => $form,
            'Group' => $Group,
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
        $user = $this->get('core.session.tenant')->getUser();
        return array(
            'contracts' => $user->getContracts(),
            'status'    => $status,
        );
    }

    /**
     * @Template()
     * @return multitype:
     */
    public function tenantAction()
    {
        $user = $this->get('core.session.tenant')->getUser();
        return array(
            'contracts' => $user->getContracts(),
        );
    }
}
