<?php
namespace RentJeeves\ComponentBundle\Controller;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ContractStatus;
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
        $date = new \DateTime();
        $start = $date->format('m/d/Y');
        $date->modify('+1 year');
        $end = $date->format('m/d/Y');
        return array(
            'form'      => $form,
            'Group'     => $Group,
            'canInvite' => $canInvite,
            'start'     => $start,
            'end'       => $end
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
        $tenant = $this->getUser();
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.default_entity_manager');
        $contracts = $em->getRepository('RjDataBundle:Contract')
            ->findByTenantIdInvertedStatusesForPayments($tenant->getId());
        $contractsArr = array();
        $activeContracts = array();
        $paidForArr = array();
        /** @var $contract Contract */
        foreach ($contracts as $contract) {
            $contractsArr[] = $contract->getDatagridRow($em);
            if (!in_array($contract->getStatus(), array(ContractStatus::FINISHED, ContractStatus::PENDING))) {
                $activeContracts[] = $contract;
                $paidForArr[$contract->getId()] = $this->get('checkout.paid_for')->getArray($contract);
            }
        }

        return array(
            'contractsRaw' => new ArrayCollection($activeContracts),
            'contracts'    => $contractsArr,
            'paidForArr'   => $paidForArr,
            'user'         => $tenant,
        );
    }
}
