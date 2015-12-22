<?php
namespace RentJeeves\ComponentBundle\Controller;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializationContext;
use RentJeeves\CheckoutBundle\Constraint\DayRangeValidator;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\PublicBundle\Controller\PublicController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\CoreBundle\DateTime;
use Symfony\Component\Form\FormView;

class ContractsListController extends Controller
{
    /**
     * @param Group $Group
     * @param FormView $form
     * @param string $searchText
     * @param string $searchColumn
     *
     * @Template("RjComponentBundle:ContractsList:landlord.html.twig")
     * @return mixed
     */
    public function indexAction(Group $Group, FormView $form, $searchText = null, $searchColumn = null)
    {
        /** @var $user Landlord */
        $user = $this->getUser();
        /** @var $group Group */
        $group = $user->getCurrentGroup();
        $canInvite = false;

        if (!empty($group)) {
            $depositAccount = $group->getRentDepositAccountForCurrentPaymentProcessor();
            $merchantName = $depositAccount ? $depositAccount->getMerchantName() : '';
            $canInvite = (!empty($merchantName)) ? true : false;
        }
        $date = new DateTime();
        $start = $date->format('m/d/Y');
        $date->modify('+1 year');
        $end = $date->format('m/d/Y');

        return [
            'form'          => $form,
            'Group'         => $Group,
            'canInvite'     => $canInvite,
            'start'         => $start,
            'end'           => $end,
            'isIntegrated'  => $Group->getGroupSettings()->getIsIntegrated(),
            'searchText'    => $searchText,
            'searchColumn'  => $searchColumn
        ];
    }

    /**
     * @param Group $group
     *
     * @Template()
     */
    public function actionsAction(Group $Group)
    {
        return [];
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
    public function tenantAction($mobile = false)
    {
        $tenant = $this->getUser();
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.default_entity_manager');
        $contracts = $em->getRepository('RjDataBundle:Contract')
            ->findByTenantIdInvertedStatusesForPayments($tenant->getId());
        $contractsArr = [];
        $activeContracts = [];
        $paidForArr = [];
        $isNewUser = false;
        $isInPaymentWindow = false;
        $isInPayAnythingWindow = false;
        $defaultPayAnythingParams = [];
        $hasIntegratedBalance = false;
        $allowPayAnything = false;
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

        $shouldShowRent = false;

        /** @var $contract Contract */
        foreach ($contracts as $contract) {
            $contractsArr[] = $contract->getDatagridRow($em);
            if (!in_array($contract->getStatus(), [ContractStatus::FINISHED, ContractStatus::PENDING])) {
                $activeContracts[] = $contract;
                $paidForArr[$contract->getId()] = $this->get('checkout.paid_for')->getArray($contract);
            }
            if (!$hasIntegratedBalance && $contract->getGroupSettings()->getIsIntegrated() === true) {
                $hasIntegratedBalance = true;
            }
            if (!$shouldShowRent && $contract->getGroupSettings()->isShowRentOnDashboard()) {
                $shouldShowRent = true;
            }
            if (($contract->getStatus() !== ContractStatus::FINISHED) &&
                end($contractsArr)['is_allowed_to_pay_anything']
            ) {
                $activeContracts[] = $contract;
            }
            if (!$allowPayAnything && end($contractsArr)['is_allowed_to_pay_anything']) {
                $allowPayAnything = true;
            }
        }

        $session = $this->get('session');
        if ($allowPayAnything && $session->has(PublicController::SESSION_CREATE_INTEGRATION_USER)) {
            $isInPayAnythingWindow  = true;
            $integrationParams = $session->get(PublicController::SESSION_CREATE_INTEGRATION_USER);

            $defaultPayAnythingParams['amounts'] = $integrationParams['amounts'] ?: [];
            $defaultPayAnythingParams['amounts'] = $integrationParams['amounts'] ?: [];
            if (isset($integrationParams['amounts'][DepositAccountType::SECURITY_DEPOSIT])) {
                $defaultPayAnythingParams['payFor'] = DepositAccountType::SECURITY_DEPOSIT;
            }
            if (isset($integrationParams['amounts'][DepositAccountType::APPLICATION_FEE])) {
                $defaultPayAnythingParams['payFor'] = DepositAccountType::APPLICATION_FEE;
            }
        }

        $contractsJson = $this->get('jms_serializer')->serialize(
            $activeContracts,
            'json',
            SerializationContext::create()->setGroups(['payRent'])
        );
        $defaultPayAnythingParamsJson = $this->get('jms_serializer')->serialize(
            $defaultPayAnythingParams,
            'json'
        );

        $pageVars = [
            'contractsJson' => $contractsJson,
            'contracts' => $contractsArr,
            'paidForArr' => $paidForArr,
            'user' => $tenant,
            'isNewUser' => $isNewUser,
            'shouldShowRent' => $shouldShowRent,
            'hasIntegratedBalance' => $hasIntegratedBalance,
            'isInPaymentWindow' => $isInPaymentWindow,
            'allowPayAnything' => $allowPayAnything,
            'isInPayAnythingWindow' => $isInPayAnythingWindow,
            'defaultPayAnythingParams' => $defaultPayAnythingParamsJson,
        ];

        if ($mobile) {
            return $this->render('RjComponentBundle:ContractsList:tenant.mobile.html.twig', $pageVars);
        } else {
            return $pageVars;
        }
    }
}
