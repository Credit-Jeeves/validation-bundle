<?php
namespace RentJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ContractsListController extends Controller
{
    /**
     * @Template("RjComponentBundle:ContractsList:landlord.html.twig")
     * @return mixed
     */
    public function indexAction(\CreditJeeves\DataBundle\Entity\Group $Group, $form)
    {
        return array(
            'form'  => $form,
            'Group' => $Group,
        );
    }

    /**
     * @Template()
     */
    public function actionsAction(\CreditJeeves\DataBundle\Entity\Group $Group)
    {
        return array(
        );
    }

    /**
     * @Template()
     */
    public function tenantNewAction($status)
    {
        $user = $this->get('core.session.tenant')->getUser();

        return array(
            'contracts' => $user->getActiveContracts(),
            'status'    => $status,
        );
    }

    /**
     * @Template()
     */
    public function tenantAction()
    {
        $tenant = $this->get('core.session.tenant')->getUser();
        $contracts = $tenant->getActiveContracts();
        $data = array();
        foreach ($contracts as $contract) {
            $data[] = $contract->getDatagridRow();
        }
        return array(
            'contracts' => $data,
        );
    }
}
