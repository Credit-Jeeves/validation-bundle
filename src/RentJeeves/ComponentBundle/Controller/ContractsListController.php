<?php
namespace RentJeeves\ComponentBundle\Controller;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializationContext;
use RentJeeves\CheckoutBundle\Constraint\DayRangeValidator;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ContractStatus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\CoreBundle\DateTime;

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
        $date = new DateTime();
        $start = $date->format('m/d/Y');
        $date->modify('+1 year');
        $end = $date->format('m/d/Y');

        return array(
            'form'          => $form,
            'Group'         => $Group,
            'canInvite'     => $canInvite,
            'start'         => $start,
            'end'           => $end,
            'isIntegrated'  => $Group->getGroupSettings()->getIsIntegrated(),
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
        $isNewUser = false;
        $isInPaymentWindow = false;
        $hasIntegratedBalance = false;
        if ($contracts && 1 == count($contracts) && $contracts[0]->getStatus() == ContractStatus::INVITE) {
            $isNewUser = true;
            /** @var Contract $contract */
            $contract = $contracts[0];
            $isInPaymentWindow = DayRangeValidator::inRange(
                new DateTime(),
                $contract->getGroup()->getGroupSettings()->getOpenDate(),
                $contract->getGroup()->getGroupSettings()->getCloseDate()
            );
        }

        /** @var $contract Contract */
        foreach ($contracts as $contract) {
            $contractsArr[] = $contract->getDatagridRow($em);
            if (!in_array($contract->getStatus(), array(ContractStatus::FINISHED, ContractStatus::PENDING))) {
                $activeContracts[] = $contract;
                $paidForArr[$contract->getId()] = $this->get('checkout.paid_for')->getArray($contract);
            }
            if (!$hasIntegratedBalance && $contract->getGroup()->getGroupSettings()->getIsIntegrated() === true) {
                $hasIntegratedBalance = true;
            }
        }

        $contractsJson = $this->get('jms_serializer')->serialize(
            $activeContracts,
            'json',
            SerializationContext::create()->setGroups(array('payRent'))
        );

        return array(
            'contractsJson' => $contractsJson,
            'contracts'     => $contractsArr,
            'paidForArr'    => $paidForArr,
            'user'          => $tenant,
            'isNewUser'     => $isNewUser,
            'hasIntegratedBalance' => $hasIntegratedBalance,
            'isInPaymentWindow' => $isInPaymentWindow,
        );
    }
}
