<?php
namespace RentJeeves\CoreBundle\Mailer;

use CreditJeeves\CoreBundle\Mailer\Mailer as BaseMailer;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\User;
use Hip\MandrillBundle\Dispatcher;
use Hip\MandrillBundle\Message;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class Mailer extends BaseMailer
{
    /**
     * @var array
     */
    protected $domains = [
        'my.renttrack.com',
        'www.renttrack.com',
        'renttrack.com',
    ];

    /**
     * @param string $templateName
     * @param array $params
     * @param string $emailTo
     * @param string $culture
     *
     * @return bool
     */
    public function sendBaseLetter($templateName, $params, $emailTo, $culture)
    {
        if (false == $this->isValidEmail($emailTo)) {
            $this->handleException(
                new \InvalidArgumentException(sprintf('"%s": this value is not a valid email address.', $templateName))
            );

            return false;
        }
        /** \Rj\EmailBundle\Entity\EmailTemplate $template */
        if (null == $template = $this->manager->findTemplateByName($templateName . '.html')) {
            $this->handleException(
                new \InvalidArgumentException(sprintf('Template with name "%s" not found', $templateName))
            );

            return false;
        }

        $recipientUser = $this->getUserByEmail($emailTo);
        $params = $this->prepareParameters($params, $recipientUser);

        try {
            $htmlContent = $this->manager->renderEmail($template->getName(), $culture, $params);

            $mandrillMessage = $this->createMandrillMessage();
            $mandrillMessage
                ->setFromEmail($htmlContent['fromEmail'])
                ->setFromName($htmlContent['fromName'])
                ->addTo($emailTo)
                ->setSubject($htmlContent['subject']);
            // Add tags and Metadata for Mandrill`s template
            if (null !== $recipientUser) {
                $mandrillMessage
                    ->addTag($recipientUser->getType())
                    ->addMetadata(['user_id' => $recipientUser->getId()]);
            }

            if (false == $mandrillSlug = $template->getEnTranslation()->getMandrillSlug()) {
                $mandrillMessage->setHtml($htmlContent['body']);
                $this->getMandrillMailer()->send($mandrillMessage);
            } else {
                // Add params for Mandrill`s template
                foreach ($params as $key => $param) {
                    $mandrillMessage->addGlobalMergeVar($key, $param);
                }
                $this->getMandrillMailer()->send($mandrillMessage, $mandrillSlug);
            }

            return true;
        } catch (\Twig_Error_Runtime $e) {
            $this->handleException($e);
        } catch (\Mandrill_Error $e) {
            $this->container->get('logger')->alert(sprintf
                (
                    'The MandrillLetter has not been sent : %s',
                    $e->getMessage()
                )
            );
            $this->handleException($e);
        }

        return false;
    }

    /**
     * @param array $params
     * @param User $user
     *
     * @return array
     */
    protected function prepareParameters(array $params, User $user = null)
    {
        // $params is second for higher priority (for test email)
        $params = array_merge($this->defaultValuesForEmail, $params);
        if (null !== $user) {
            if (false != $partner = $user->getPartner()) {
                if (true === $partner->isPoweredBy()) {
                    $params['logoName'] = $partner->getLogoName();
                    $params['partnerName'] = $partner->getName();
                    $params['partnerAddress'] = $partner->getAddress();
                    $params['loginUrl'] = $partner->getLoginUrl();
                    $params['isPoweredBy'] = $partner->isPoweredBy();
                }
            }
        }

        return $params;
    }

    /**
     * Create new Mandrill Message with needed header
     *
     * @return Message
     */
    protected function createMandrillMessage()
    {
        $mandrillMessage = new Message();
        $mandrillMessage
            ->setTrackClicks(true)
            ->setTrackOpens(true)
            ->setUrlStripQs(true);
        foreach ($this->domains as $domain) {
            $mandrillMessage->addGoogleAnalyticsDomain($domain);
        }

        return $mandrillMessage;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function sendRjCheckEmail(User $user)
    {
        $url = $this->container->get('router')->generate('tenant_new_check', ['code' => $user->getInviteCode()], true);

        return $this->sendEmail($user, 'rjCheck', ['checkUrl' => $url]);
    }

    /**
     * @param Landlord $landlord
     * @param Tenant $tenant
     * @param Contract $contract
     *
     * @return bool
     */
    public function sendRjLandLordInvite(Landlord $landlord, Tenant $tenant, Contract $contract)
    {
        $vars = [
            'nameLandlord' => $landlord->getFirstName(),
            'fullNameTenant' => $tenant->getFullName(),
            'nameTenant' => $tenant->getFirstName(),
            'address' => $contract->getProperty() ? $contract->getProperty()->getAddress() : null,
            'unitName' => $contract->getUnit() ? $contract->getUnit()->getName() : $contract->getSearch(),
            'inviteCode' => $landlord->getInviteCode(),
        ];

        return $this->sendBaseLetter('rjLandLordInvite', $vars, $landlord->getEmail(), $landlord->getCulture());
    }

    /**
     * @param Tenant $tenant
     * @param Landlord $landlord
     * @param Contract $contract
     * @param string $isImported
     *
     * @return bool
     */
    public function sendRjTenantInvite(Tenant $tenant, Landlord $landlord, Contract $contract, $isImported = null)
    {
        $vars = [
            'fullNameLandlord' => $landlord->getFullName(),
            'groupName' => $contract->getGroup()->getName(),
            'holdingName' => $contract->getGroup()->getHolding()->getName(),
            'nameTenant' => $tenant->getFirstName(),
            'address' => $contract->getProperty()->getAddress(),
            'rentAddress' => $contract->getRentAddress(),
            'unitName' => $contract->getUnit() ? $contract->getUnit()->getName() : '',
            'inviteCode' => $tenant->getInviteCode(),
            'isImported' => $isImported,
        ];

        return $this->sendBaseLetter('rjTenantInvite', $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    /**
     * @param Tenant $tenant
     * @param Landlord $landlord
     * @param Contract $contract
     *
     * @return bool
     */
    public function sendRjTenantLatePayment(Tenant $tenant, Landlord $landlord, Contract $contract)
    {
        $vars = [
            'groupName' => $contract->getGroup()->getName(),
            'holdingName' => $contract->getGroup()->getHolding()->getName(),
            'fullNameLandlord' => $landlord->getFullName(),
            'nameTenant' => $tenant->getFirstName(),
            'address' => $contract->getProperty()->getAddress(),
            'unitName' => $contract->getUnit()->getName(),
            'inviteCode' => $tenant->getInviteCode(),
        ];

        return $this->sendBaseLetter('rjTenantLatePayment', $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    /**
     * @param Tenant $tenant
     * @param Landlord $landlord
     * @param Contract $contract
     *
     * @return bool
     */
    public function sendRjLandlordComeFromInvite(Tenant $tenant, Landlord $landlord, Contract $contract)
    {
        $unitName = $contract->getUnit() ? $contract->getUnit()->getName() : $contract->getSearch();
        $vars = [
            'nameTenant' => $tenant->getFirstName(),
            'fullNameLandlord' => $landlord->getFullName(),
            'address' => $contract->getProperty()->getAddress(),
            'unitName' => $unitName,
            'rentAmount' => $contract->getRent(),
        ];

        return $this->sendBaseLetter('rjLandlordComeFromInvite', $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    /**
     * @param Tenant $tenant
     * @param Holding $holding
     * @param Contract $contract
     * @param string $paymentType
     *
     * @return bool
     */
    public function sendRjPaymentDue(Tenant $tenant, Holding $holding, Contract $contract, $paymentType = null)
    {
        $vars = [
            'nameHolding' => $holding->getName(),
            'nameTenant' => $tenant->getFullName(),
            'address' => $contract->getRentAddress($contract->getProperty(), $contract->getUnit()),
            'paymentType' => $paymentType,
        ];

        return $this->sendBaseLetter('rjPaymentDue', $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    /**
     * @param Landlord $landlord
     * @param Tenant $tenant
     * @param Contract $contract
     *
     * @return bool
     */
    public function sendPendingContractToLandlord(Landlord $landlord, Tenant $tenant, Contract $contract)
    {
        $vars = [
            'nameLandlord' => $landlord->getFullName(),
            'nameTenant' => $tenant->getFullName(),
            'address' => $contract->getRentAddress($contract->getProperty(), $contract->getUnit()),
        ];

        return $this->sendBaseLetter('rjPendingContract', $vars, $landlord->getEmail(), $landlord->getCulture());
    }

    /**
     * @param Landlord $landlord
     * @param float $amount
     * @param string $sTemplate
     *
     * @return bool
     */
    public function sendTodayPayments(Landlord $landlord, $amount, $sTemplate = 'rjTodayPayments')
    {
        $vars = [
            'nameLandlord' => $landlord->getFullName(),
            'amount' => $amount,
        ];

        return $this->sendBaseLetter($sTemplate, $vars, $landlord->getEmail(), $landlord->getCulture());
    }

    /**
     * @param Landlord $landlord
     * @param $report
     *
     * @return bool
     */
    public function sendRjDailyReport(Landlord $landlord, $report)
    {
        $vars = [
            'nameLandlord' => $landlord->getFullName(),
            'report' => $report,
        ];

        return $this->sendBaseLetter('rjDailyReport', $vars, $landlord->getEmail(), $landlord->getCulture());
    }

    /**
     * @param Tenant $tenant
     * @param Contract $contract
     * @param string $diff
     *
     * @return bool
     */
    public function sendRjTenantLateContract(Tenant $tenant, Contract $contract, $diff)
    {
        $vars = [
            'nameTenant' => $tenant->getFullName(),
            'diff' => $diff,
            'address' => $contract->getRentAddress($contract->getProperty(), $contract->getUnit()),
        ];

        return $this->sendBaseLetter('rjTenantLateContract', $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    /**
     * @param Landlord $landlord
     * @param array $tenants
     *
     * @return bool
     */
    public function sendListLateContracts(Landlord $landlord, array $tenants)
    {
        $vars = [
            'nameLandlord' => $landlord->getFullName(),
            'tenants' => $tenants,
        ];

        return $this->sendBaseLetter('rjListLateContracts', $vars, $landlord->getEmail(), $landlord->getCulture());
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    public function sendRentReceipt(Order $order)
    {
        $tenant = $order->getUser();
        $history = $order->getCompleteTransaction();
        $fee = $order->getFee();
        $amount = $order->getSum();
        $total = $fee + $amount;

        $vars = [
            'nameTenant' => $tenant->getFullName(),
            'datetime' => $order->getUpdatedAt()->format('m/d/Y H:i:s'),
            'transactionID' => $history ? $history->getTransactionId() : 'N/A',
            'amount' => $amount,
            'fee' => $fee,
            'total' => $total,
            'groupName' => $order->getGroupName(),
            'rentAmount' => $order->getRentAmount(),
            'otherAmount' => $order->getOtherAmount(),
            'paymentProcessor' => $order->getPaymentProcessor(),
            'type' => $order->getPaymentType(),
            'statementDescriptor' => $this->getStatementDescriptor($order),
        ];

        return $this->sendBaseLetter('rjOrderReceipt', $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    public function sendRentError(Order $order)
    {
        $tenant = $order->getUser();
        $fee = $order->getFee();
        $amount = $order->getSum();
        $total = $fee + $amount;
        $vars = [
            'nameTenant' => $tenant->getFullName(),
            'datetime' => $order->getUpdatedAt()->format('m/d/Y H:i:s'),
            'amount' => $amount,
            'fee' => $fee,
            'total' => $total,
            'groupName' => $order->getGroupName(),
            'orderId' => $order->getId(),
            'error' => $order->getErrorMessage(),
            'transactionId' => $order->getHeartlandTransactionId(),
            'rentAmount' => $order->getRentAmount(),
            'otherAmount' => $order->getOtherAmount(),
            'orderType' => $order->getPaymentType(),
        ];

        return $this->sendBaseLetter('rjOrderError', $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    /**
     * @param Tenant $tenant
     * @param Landlord $landlord
     * @param Contract $contract
     *
     * @return bool
     */
    public function sendRjTenantInviteReminder(Tenant $tenant, Landlord $landlord, Contract $contract)
    {
        $vars = [
            'fullNameLandlord' => $landlord->getFullName(),
            'groupName' => $contract->getGroup()->getName(),
            'holdingName' => $contract->getGroup()->getHolding()->getName(),
            'nameTenant' => $tenant->getFirstName(),
            'address' => $contract->getProperty()->getAddress(),
            'unitName' => $contract->getUnit() ? $contract->getUnit()->getName() : '',
            'inviteCode' => $tenant->getInviteCode(),
        ];

        return $this->sendBaseLetter('rjTenantInviteReminder', $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    /**
     * @param Contract $contract
     *
     * @return bool
     */
    public function sendContractApprovedToTenant(Contract $contract)
    {
        $tenant = $contract->getTenant();
        $vars = ['nameTenant' => $tenant->getFullName()];

        return $this->sendBaseLetter('rjContractApproved', $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    /**
     * @param Tenant $tenant
     * @param Landlord $landlord
     * @param Contract $contract
     *
     * @return bool
     */
    public function sendRjContractRemovedFromDbByLandlord(Tenant $tenant, Landlord $landlord, Contract $contract)
    {
        $vars = [
            'fullNameLandlord' => $landlord->getFullName(),
            'fullNameTenant' => $tenant->getFullName(),
            'address' => $contract->getProperty()->getAddress(),
            'unitName' => $contract->getUnit() ? $contract->getUnit()->getName() : '',
        ];

        return $this->sendBaseLetter(
            'rjContractRemovedFromDbByLandlord',
            $vars,
            $tenant->getEmail(),
            $tenant->getCulture()
        );
    }

    /**
     * @param Tenant $tenant
     * @param Landlord $landlord
     * @param Contract $contract
     *
     * @return bool
     */
    public function sendRjContractRemovedFromDbByTenant(Tenant $tenant, Landlord $landlord, Contract $contract)
    {
        $vars = [
            'fullNameLandlord' => $landlord->getFullName(),
            'fullNameTenant' => $tenant->getFullName(),
            'address' => $contract->getProperty()->getAddress(),
            'unitName' => $contract->getUnit() ? $contract->getUnit()->getName() : '',
        ];

        return $this->sendBaseLetter(
            'rjContractRemovedFromDbByTenant',
            $vars,
            $landlord->getEmail(),
            $landlord->getCulture()
        );
    }

    /**
     * @param User $user
     * @param Group $group
     *
     * @return bool
     */
    public function merchantNameSetuped(User $user, Group $group)
    {
        $vars = [
            'fullNameLandlord' => $user->getFullName(),
            'groupName' => $group->getName(),
        ];

        return $this->sendBaseLetter('rjMerchantNameSetuped', $vars, $user->getEmail(), $user->getCulture());
    }

    /**
     * @param Contract $contract
     * @param Landlord $landlord
     * @param Tenant $tenant
     *
     * @return bool
     */
    public function endContractByLandlord(Contract $contract, Landlord $landlord, Tenant $tenant)
    {
        // Unit is a Doctrine Proxy, it always exists, but it throws an exception when we try to get unit's name
        try {
            $unitName = $contract->getUnit()->getName();
        } catch (\Exception $e) {
            $unitName = '';
        }
        $vars = [
            'tenantFullName' => $tenant->getFullName(),
            'landlordFullName' => $landlord->getFullName(),
            'uncollectedBalance' => $contract->getUncollectedBalance(),
            'address' => $contract->getProperty()->getAddress(),
            'unitName' => $unitName,
        ];

        return $this->sendBaseLetter('rjEndContract', $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    public function sendOrderCancelToTenant(Order $order)
    {
        $tenant = $order->getContract()->getTenant();

        $vars = [
            'tenantFullName' => $tenant->getFullName(),
            'orderStatus' => $order->getStatus(),
            'rentAmount' => $order->getSum(),
            'orderDate' => $order->getUpdatedAt()->format('m/d/Y H:i:s'),
            'reversalDescription' => $order->getReversalDescription(),
        ];

        return $this->sendBaseLetter('rjOrderCancel', $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    /**
     * @param Order $order
     */
    public function sendOrderCancelToLandlord(Order $order)
    {
        $tenant = $order->getContract()->getTenant();
        $vars = [
            'landlordFirstName' => '',
            'orderStatus' => $order->getStatus(),
            'rentAmount' => $order->getSum(),
            'orderDate' => $order->getUpdatedAt()->format('m/d/Y H:i:s'),
            'tenantName' => $tenant->getFullName(),
            'reversalDescription' => $order->getReversalDescription(),
        ];

        $group = $order->getContract()->getGroup();
        /** @var Landlord $landlord */
        foreach ($group->getGroupAgents() as $landlord) {
            $vars['landlordFirstName'] = $landlord->getFirstName();
            $this->sendBaseLetter('rjOrderCancelToLandlord', $vars, $landlord->getEmail(), $landlord->getCulture());
        }
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    public function sendPendingInfo(Order $order)
    {
        $tenant = $order->getContract()->getTenant();
        $transaction = $order->getCompleteTransaction();
        $amount = $order->getSum();
        $fee = $order->getFee();
        $total = $fee + $amount;
        $vars = [
            'tenantName' => $tenant->getFullName(),
            'orderTime' => $order->getUpdatedAt()->format('m/d/Y H:i:s'),
            'transactionID' => $transaction ? $transaction->getTransactionId() : 'N/A',
            'amount' => $amount,
            'fee' => $fee,
            'total' => $total,
            'groupName' => $order->getGroupName(),
            'rentAmount' => $order->getRentAmount(),
            'otherAmount' => $order->getOtherAmount(),
            'paymentProcessor' => $order->getPaymentProcessor(),
            'type' => $order->getPaymentType(),
            'statementDescriptor' => $this->getStatementDescriptor($order),
        ];

        return $this->sendBaseLetter('rjPendingOrder', $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    /**
     * @param Contract $contract
     * @param Payment $payment
     *
     * @return bool
     */
    public function sendContractAmountChanged(Contract $contract, Payment $payment)
    {
        $tenant = $contract->getTenant();
        $vars = [
            'tenantName' => $tenant->getFullName(),
            'rentAmount' => $contract->getRent(),
            'paymentAmount' => $payment->getAmount(),
            'groupName' => $contract->getGroup()->getName(),
            'holdingName' => $contract->getGroup()->getHolding()
        ];

        return $this->sendBaseLetter('rjContractAmountChanged', $vars, $tenant->getEmail(), $tenant->getCulture());
    }

    /**
     * @param Landlord $landlord
     * @param array $groups
     * @param \DateTime $date
     * @param string $resend
     *
     * @return bool
     */
    public function sendBatchDepositReportHolding(Landlord $landlord, $groups, \DateTime $date, $resend = null)
    {
        $vars = [
            'landlordFirstName' => $landlord->getFirstName(),
            'date' => $date,
            'groups' => $groups,
            'resend' => $resend,
        ];

        return $this->sendBaseLetter(
            'rjBatchDepositReportHolding',
            $vars,
            $landlord->getEmail(),
            $landlord->getCulture()
        );
    }

    /**
     * @param Landlord $landlord
     * @param Group $group
     * @param \DateTime $date
     * @param array $batches
     * @param $returns
     * @param $resend
     *
     * @return bool
     */
    public function sendBatchDepositReportLandlord(
        Landlord $landlord,
        Group $group,
        \DateTime $date,
        $batches,
        $returns,
        $resend = null
    ) {
        $vars = [
            'landlordFirstName' => $landlord->getFirstName(),
            'date' => $date,
            'groupName' => $group->getName(),
            'groupPaymentProcessor' => $group->getGroupSettings()->getPaymentProcessor(),
            'accountNumber' => $group->getAccountNumber(),
            'batches' => $batches,
            'returns' => $returns,
            'resend' => $resend,
        ];

        return $this->sendBaseLetter(
            'rjBatchDepositReportLandlord',
            $vars,
            $landlord->getEmail(),
            $landlord->getCulture()
        );
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    public function sendReportReceipt(Order $order)
    {
        $dateShortFormat = $this->container->getParameter('date_short');

        return $this->sendEmail(
            $order->getUser(),
            'rjReceipt',
            [
                'tenantName' => $order->getUser()->getFullName(),
                'date' => $order->getCreatedAt()->format($dateShortFormat),
                'amout' => $this->container->getParameter('credittrack_payment_per_month_currency') .
                    $this->container->getParameter('credittrack_payment_per_month'), // TODO currency formatting
                'number' => $order->getHeartlandTransactionId(),
                'paymentProcessor' => $order->getPaymentProcessor(),
                'type' => $order->getPaymentType(),
                'statementDescriptor' => $this->getStatementDescriptor($order),
            ]
        );
    }

    /**
     * @param Landlord $landlord
     * @param array $data
     *
     * @return bool
     */
    public function sendPushBatchReceiptsReport(Landlord $landlord, array $data)
    {
        return $this->sendBaseLetter(
            $template = 'rjPushBatchReceiptsReport',
            ['data' => $data],
            $landlord->getEmail(),
            $landlord->getCulture()
        );
    }

    /**
     * @param Tenant $tenant
     *
     * @return bool
     */
    public function sendEmailAcceptYardiPayment(Tenant $tenant)
    {
        $context = $this->container->get('router')->getContext();
        $context->setHost($this->container->getParameter('server_name_rj'));

        $url = $this->container->get('router')->generate(
            'fos_user_security_login',
            [],
            true
        );

        return $this->sendBaseLetter(
            $template = 'rjYardiPaymentAcceptedTurnOn',
            [
                'TenantName' => $tenant->getFullName(),
                'href' => $url,
            ],
            $tenant->getEmail(),
            $tenant->getCulture()
        );
    }

    /**
     * @param Tenant $tenant
     *
     * @return bool
     */
    public function sendEmailDoNotAcceptYardiPayment(Tenant $tenant)
    {
        return $this->sendBaseLetter(
            $template = 'rjYardiPaymentAcceptedTurnOff',
            ['TenantName' => $tenant->getFullName()],
            $tenant->getEmail(),
            $tenant->getCulture()
        );
    }

    /**
     * @param Landlord $landlord
     * @param Contract[] $contracts
     * @param string $month
     *
     * @return bool
     */
    public function sendLateReportingReviewEmailToLandlord(Landlord $landlord, array $contracts, $month)
    {
        return $this->sendBaseLetter(
            $template = 'rjLateReportingLandlord',
            [
                'landlordName' => $landlord->getFullName(),
                'contracts' => $contracts,
                'month' => $month,
            ],
            $landlord->getEmail(),
            $landlord->getCulture()
        );
    }

    /**
     * @param Tenant $tenant
     * @param string $month
     *
     * @return bool
     */
    public function sendLateReportingReviewEmailToTenant(Tenant $tenant, $month)
    {
        return $this->sendBaseLetter(
            $template = 'rjLateReportingTenant',
            [
                'tenantName' => $tenant->getFullName(),
                'month' => $month,
            ],
            $tenant->getEmail(),
            $tenant->getCulture()
        );
    }

    /**
     * @param Order $order
     * @return string
     */
    protected function getStatementDescriptor(Order $order)
    {
        $statementDescriptorPrefix = $order->getPaymentProcessor() == PaymentProcessor::HEARTLAND ? 'RENTTRK' : 'ORC';
        $statementDescriptor = $order->getContract() ? $order->getContract()->getGroup()->getStatementDescriptor() : '';

        return sprintf('%s*%s', $statementDescriptorPrefix, $statementDescriptor);
    }


    /**
     * @param string $email
     *
     * @return bool
     */
    protected function isValidEmail($email)
    {
        $errors = $this->container->get('validator')->validateValue(
            $email,
            [new Email(), new NotBlank()]
        );
        if (count($errors) > 0) {
            return false;
        }

        return true;
    }

    /**
     * @return Dispatcher
     */
    protected function getMandrillMailer()
    {
        return $this->container->get('hip_mandrill.dispatcher');
    }
}
