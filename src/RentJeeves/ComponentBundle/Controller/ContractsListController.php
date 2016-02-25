<?php
namespace RentJeeves\ComponentBundle\Controller;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializationContext;
use RentJeeves\CheckoutBundle\Constraint\DayRangeValidator;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\PublicBundle\Services\AccountingSystemIntegrationDataManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\CoreBundle\DateTime;
use Symfony\Component\Form\FormView;

class ContractsListController extends Controller
{
    /**
     * @param FormView $form
     * @param string $searchText
     * @param string $searchColumn
     *
     * @Template("RjComponentBundle:ContractsList:landlord.html.twig")
     * @return mixed
     */
    public function indexAction(FormView $form, $searchText = null, $searchColumn = null)
    {
        /** @var $group Group */
        $group = $this->get('core.session.landlord')->getGroup();
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
            'form' => $form,
            'group' => $group,
            'canInvite' => $canInvite,
            'start' => $start,
            'end' => $end,
            'isAllowedEditResidentId' => $group->isAllowedEditResidentId(),
            'isAllowedEditLeaseId' => $group->isAllowedEditLeaseId(),
            'isIntegrated' => $group->getGroupSettings()->getIsIntegrated(),
            'searchText' => $searchText,
            'searchColumn' => $searchColumn
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
        $orderRepo = $em->getRepository('DataBundle:Order');
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
            if (!in_array($contract->getStatus(), [ContractStatus::FINISHED, ContractStatus::PENDING]) &&
                end($contractsArr)['payment_status'] != 'duplicated'
            ) {
                $activeContracts[] = $contract;
                $paidForArr[$contract->getId()] = $this->get('checkout.paid_for')->getArray($contract);
            }
            if (!$hasIntegratedBalance && $contract->getGroupSettings()->getIsIntegrated() === true) {
                $hasIntegratedBalance = true;
            }
            if (!$shouldShowRent && $contract->getGroupSettings()->isShowRentOnDashboard()) {
                $shouldShowRent = true;
            }

            if ($contract->getGroup()->getOrderAlgorithm() === OrderAlgorithmType::PAYDIRECT &&
                $lastDTROrder = $orderRepo->getLastDTRPaymentByContract($contract)
            ) {
                $lastPaymentDate = clone $lastDTROrder->getCreatedAt();
                $lastPaymentDate->modify(
                    '+' . (int) $this->container->getParameter('dod_dtr_payment_rolling_window') . ' days'
                );
                $contract->setPaymentMinStartDate($lastPaymentDate);
            }

            if (($contract->getStatus() !== ContractStatus::FINISHED) &&
                end($contractsArr)['is_allowed_to_pay_anything'] &&
                end($contractsArr)['payment_status'] != 'duplicated'
            ) {
                $activeContracts[] = $contract;
            }
            if (!$allowPayAnything && end($contractsArr)['is_allowed_to_pay_anything']) {
                $allowPayAnything = true;
            }
            // TODO Fixed inside RT-2125
            if (end($contractsArr)['payment_status'] == 'duplicated') {
                $this->get('logger')->alert(
                    sprintf(
                        'Detected more than one active payments for a contract (#%d).' .
                        ' Please resolve ASAP or the tenant could be charged twice!',
                        $contract->getId()
                    )
                );
            }
        }

        /** @var AccountingSystemIntegrationDataManager $integrationDataManager */
        $integrationDataManager = $this->get('accounting_system.integration.data_manager');
        if ($allowPayAnything && $integrationDataManager->hasIntegrationData()) {
            $isInPayAnythingWindow  = true;

            $defaultPayAnythingParams['amounts'] = $integrationDataManager->getAmounts();
            if (!empty($defaultPayAnythingParams['amounts'][DepositAccountType::SECURITY_DEPOSIT])) {
                $defaultPayAnythingParams['payFor'] = DepositAccountType::SECURITY_DEPOSIT;
            }
            if (!empty($defaultPayAnythingParams['amounts'][DepositAccountType::APPLICATION_FEE])) {
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
