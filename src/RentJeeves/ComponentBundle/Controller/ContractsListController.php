<?php
namespace RentJeeves\ComponentBundle\Controller;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Tenant;
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
        /** @var $user Landlord */
        $user = $this->getUser();
        /** @var $group Group */
        $group = $user->getCurrentGroup();
        $canInvite = false;


        if (!empty($group)) {
            $merchantName = $group->getMerchantName();
            $canInvite = (!empty($merchantName))? true : false;
        }

        return array(
            'form'      => $form,
            'Group'     => $Group,
            'canInvite' => $canInvite,
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
        /** @var Tenant $tenant */
        $tenant = $this->getUser();
        $contracts = $tenant->getActiveContracts();
        $em = $this->get('doctrine.orm.default_entity_manager');
        $data = array();
        /** @var $contract Contract */
        foreach ($contracts as $contract) {
            $data[] = $contract->getDatagridRow($em);
        }
        return array(
            'contractsRaw' => $contracts,
            'contracts' => $data,
            'user' => $tenant,
        );
    }
}
