<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Heartland;

use Doctrine\ORM\EntityManager;
use CreditJeeves\DataBundle\Entity\Group;
use Payum2\Heartland\Model\PaymentDetails;
use Payum2\Heartland\Soap\Base\ACHAccountType;
use Payum2\Heartland\Soap\Base\ACHDepositType;
use Payum2\Heartland\Soap\Base\GetTokenRequest;
use Payum2\Heartland\Soap\Base\GetTokenResponse;
use Payum2\Bundle\PayumBundle\Registry\ContainerAwareRegistry as Payum2AwareRegistry;
use Payum2\Heartland\Soap\Base\RegisterTokenToAdditionalMerchantRequest;
use Payum2\Payment;
use Payum2\Request\BinaryMaskStatusRequest;
use Payum2\Request\CaptureRequest;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorConfigurationException;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\Exception\InvalidAttributeNameException;
use CreditJeeves\DataBundle\Entity\User;
use RentJeeves\DataBundle\Entity\BillingAccount;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\UserAwareInterface;
use RentJeeves\DataBundle\Enum\BankAccountType;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use RentJeeves\CoreBundle\DateTime;
use Payum2\Heartland\Soap\Base\TokenPaymentMethod;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as PaymentAccountData;
use RentJeeves\DataBundle\Entity\PaymentAccount as PaymentAccountEntity;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RuntimeException;

class PaymentAccountManager
{
    /** @var Payment */
    protected $payment;

    /** @var string */
    protected $defaultMerchantName;

    /** @var  EntityManager */
    protected $em;

    /**
     * PaymentAccountManager constructor.
     * @param EntityManager $em
     * @param Payum2AwareRegistry $payum2
     * @param string $rtGroupCode
     */
    public function __construct(EntityManager $em, Payum2AwareRegistry $payum2, $rtGroupCode)
    {
        $this->payment = $payum2->getPayment('heartland');
        $this->em = $em;
        /** @var Group $group */
        $group = $em->getRepository('DataBundle:Group')->findOneByCode($rtGroupCode);
        $depositAccount = $group->getDepositAccount(DepositAccountType::RENT, PaymentProcessor::HEARTLAND);
        $this->defaultMerchantName = $depositAccount ? $depositAccount->getMerchantName() : '';
    }

    /**
     * Registers a payment token for given payment account, user and deposit account.
     *
     * @param  PaymentAccountData $paymentAccountData
     * @param  User $user
     * @param  DepositAccount $depositAccount
     * @throws PaymentProcessorConfigurationException
     */
    public function registerPaymentToken(
        PaymentAccountData $paymentAccountData,
        User $user,
        DepositAccount $depositAccount
    ) {
        $merchantName = $depositAccount->getMerchantName();

        if (empty($merchantName)) {
            throw new PaymentProcessorConfigurationException(
                'Heartland payment processor error: merchant name not found'
            );
        }
        $tokenRequest = $this->getTokenRequest($paymentAccountData, $user);
        $token = $this->getTokenResponse($tokenRequest, $merchantName);

        /** @var PaymentAccount $paymentAccount */
        $paymentAccount = $paymentAccountData->getEntity();
        $paymentAccount->setToken($token);
        $paymentAccount->setPaymentProcessor(PaymentProcessor::HEARTLAND);
        $paymentAccount->setUser($user);
        $paymentAccount->addDepositAccount($depositAccount);

        $this->em->persist($paymentAccount);
        $this->em->flush($paymentAccount);
    }

    /**
     * Registers a billing token for given payment account and user.
     *
     * @param PaymentAccountData $paymentAccountData
     * @param User $user
     */
    public function registerBillingToken(
        PaymentAccountData $paymentAccountData,
        User $user
    ) {
        $tokenRequest = $this->getTokenRequest($paymentAccountData, $user);
        $token = $this->getTokenResponse($tokenRequest, $this->defaultMerchantName);

        /** @var BillingAccount $billingAccount */
        $billingAccount = $paymentAccountData->getEntity();
        $billingAccount->setToken($token);
        $billingAccount->setPaymentProcessor(PaymentProcessor::HEARTLAND);

        $this->em->persist($billingAccount);
        $this->em->flush($billingAccount);
    }

    /**
     * TODO: change this in RT-1719: HPS: Move PaymentAccount-Deposit_account relation to PaymentAccount-MerchantName
     * Ensure there is an association between the given paymentAccount and
     * deposit account. If there isn't, form the association by requesting
     * registerTokenToAdditionalMerchant from heartland, then creating a DB
     * association between the paymentAccount and the depositAccount.
     *
     * @param  PaymentAccount $paymentAccount
     * @param  DepositAccount $depositAccount
     */
    public function ensureAccountAssociation(PaymentAccount $paymentAccount, DepositAccount $depositAccount)
    {
        if (null == $registerToMerchantName = $depositAccount->getMerchantName()) {
            throw new \RuntimeException('Cannot register to a group without a merchant name.');
        }

        $associatedDepositAccounts = $this->em->getRepository('RjDataBundle:DepositAccount')
            ->completeByPaymentAccountAndDepositAccount($paymentAccount, $depositAccount);

        if (!empty($associatedDepositAccounts)) {
            // already associated
            return;
        }

        $existingDepositAccounts = $this->em->getRepository('RjDataBundle:DepositAccount')
            ->getAssociatedForPaymentAccountAndGroup($paymentAccount, $depositAccount->getGroup());

        if (empty($existingDepositAccounts)) {
            throw new RuntimeException(
                'Registering to another deposit account only works when ' .
                'there is at least one existing association.'
            );
        }

        // any previously registered group will work
        /** @var DepositAccount[] $existingDepositAccounts */
        $merchantName = $existingDepositAccounts[0]->getMerchantName();
        $token = $paymentAccount->getToken();

        $this->registerTokenToAdditionalMerchant(
            $merchantName,
            $registerToMerchantName,
            $token
        );

        // create the association
        $paymentAccount->addDepositAccount($depositAccount);
        $this->em->persist($paymentAccount);
        $this->em->flush();
    }

    /**
     * @param  PaymentAccountData            $paymentAccountData
     * @param  User                          $user
     * @return GetTokenRequest
     * @throws InvalidAttributeNameException
     */
    protected function getTokenRequest(PaymentAccountData $paymentAccountData, User $user)
    {
        $request = new GetTokenRequest();

        /** @var PaymentAccountEntity $paymentAccountEntity */
        $paymentAccountEntity = $paymentAccountData->getEntity();
        $request->getAccountHolderData()->setEmail($user->getEmail());
        $request->getAccountHolderData()->setFirstName($user->getFirstName());
        $request->getAccountHolderData()->setLastName($user->getLastName());
        $request->getAccountHolderData()->setPhone($user->getPhone());

        if ($paymentAccountEntity instanceof UserAwareInterface && $paymentAccountEntity->getAddress()) {
            $paymentAccountEntity->getAddress()->setUser($user);
        }

        if (PaymentAccountTypeEnum::CARD == $paymentAccountEntity->getType()) {
            $ccMonth = $paymentAccountData->get('expiration_month');
            $ccYear = $paymentAccountData->get('expiration_year');
            $paymentAccountEntity->setCcExpiration(new DateTime("last day of {$ccYear}-{$ccMonth}"));

            $request->getAccountHolderData()->setNameOnCard($paymentAccountData->get('account_name'));
            $request->getAccountHolderData()->setAddress($paymentAccountEntity->getAddress()->getAddress());
            $request->getAccountHolderData()->setCity($paymentAccountEntity->getAddress()->getCity());
            $request->getAccountHolderData()->setState($paymentAccountEntity->getAddress()->getArea());
            $request->getAccountHolderData()->setZip($paymentAccountEntity->getAddress()->getZip());

            $request->setAccountNumber($paymentAccountData->get('card_number'));
            $request->setExpirationMonth($ccMonth);
            $request->setExpirationYear($ccYear);
            $request->setPaymentMethod(TokenPaymentMethod::CREDIT);
        } elseif (PaymentAccountTypeEnum::BANK == $paymentAccountEntity->getType()) {
            $fullName = trim($paymentAccountData->get('account_name'));
            $lastSpacePosition = strrpos($fullName, ' ');
            $firstName = substr($fullName, 0, $lastSpacePosition);
            $lastName = substr($fullName, $lastSpacePosition + 1);

            $request->getAccountHolderData()->setFirstName($firstName);
            $request->getAccountHolderData()->setLastName($lastName);
            $request->setRoutingNumber($paymentAccountData->get('routing_number'));
            $request->setAccountNumber($paymentAccountData->get('account_number'));

            if (BankAccountType::BUSINESS_CHECKING === $paymentAccountEntity->getBankAccountType()) {
                $request->setACHDepositType(ACHDepositType::CHECKING);
                $request->setACHAccountType(ACHAccountType::BUSINESS);
            } else {
                $request->setACHAccountType(ACHAccountType::PERSONAL);

                if (BankAccountType::SAVINGS == $paymentAccountEntity->getBankAccountType()) {
                    $request->setACHDepositType(ACHDepositType::SAVINGS);
                } else {
                    $request->setACHDepositType(ACHDepositType::CHECKING);
                }
            }

            $request->setPaymentMethod(TokenPaymentMethod::ACH);
        }

        return $request;
    }

    /**
     * @param $tokenRequest
     * @param $merchantName
     * @return string
     */
    protected function getTokenResponse($tokenRequest, $merchantName)
    {
        $paymentDetails = new PaymentDetails();
        $paymentDetails->setMerchantName($merchantName);
        $paymentDetails->setRequest($tokenRequest);
        $captureRequest = new CaptureRequest($paymentDetails);
        $this->payment->execute($captureRequest);

        $statusRequest = new BinaryMaskStatusRequest($captureRequest->getModel());
        $this->payment->execute($statusRequest);

        /** @var GetTokenResponse $response */
        $response = $statusRequest->getModel()->getResponse();

        if (!$statusRequest->isSuccess()) {
            throw new RuntimeException($paymentDetails->getMessages());
        }

        return $response->getToken();
    }

    /**
     * @param string $merchantName
     * @param string $registerToMerchantName
     * @param string $token
     * @throws \Exception
     * @throws \Payum2\Request\InteractiveRequestInterface
     */
    protected function registerTokenToAdditionalMerchant($merchantName, $registerToMerchantName, $token)
    {
        $request = new RegisterTokenToAdditionalMerchantRequest();
        $request->setToken($token);
        $request->getRegisterToMerchantCredential()->setMerchantName($registerToMerchantName);

        $paymentDetails = new PaymentDetails();
        $paymentDetails->setMerchantName($merchantName);
        $paymentDetails->setRequest($request);
        $captureRequest = new CaptureRequest($paymentDetails);

        /** @var Payment $payment */
        $payment = $this->payum->getPayment('heartland');

        $payment->execute($captureRequest);

        $statusRequest = new BinaryMaskStatusRequest($captureRequest->getModel());
        $payment->execute($statusRequest);

        /** @var GetTokenResponse $response */
        $response = $statusRequest->getModel()->getResponse();

        if (!$statusRequest->isSuccess()) {
            throw new RuntimeException($paymentDetails->getMessages());
        }
    }
}
