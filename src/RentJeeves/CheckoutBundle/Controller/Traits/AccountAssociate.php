<?php
namespace RentJeeves\CheckoutBundle\Controller\Traits;

use CreditJeeves\DataBundle\Entity\Group;
use Payum2\Heartland\Model\PaymentDetails;
use Payum2\Heartland\Soap\Base\GetTokenResponse;
use Payum2\Heartland\Soap\Base\RegisterTokenToAdditionalMerchantRequest;
use Payum2\Payment;
use Payum2\Request\BinaryMaskStatusRequest;
use Payum2\Request\CaptureRequest;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RuntimeException;

/**
 * @author Stephen Crosby <stephen@brandedcrate.com>
 * @TODO: need refactoring
 */
trait AccountAssociate
{
    /**
     * Ensure there is an association between the given paymentAccount and
     * group. If there isn't, form the association by requesting
     * registerTokenToAdditionalMerchant from heartland, then creating a DB
     * association between the paymentAccount and the group's depositAccount.
     *
     * @param  PaymentAccount $paymentAccount
     * @param  Group $group
     * @param  string $depositAccountType
     * @return bool
     */
    protected function ensureAccountAssociation(PaymentAccount $paymentAccount, Group $group, $depositAccountType)
    {
        if ($paymentAccount->getPaymentProcessor() !== PaymentProcessor::HEARTLAND) {
            return true;
        }

        $em = $this->getDoctrine()->getManager();
        if (null === $depositAccount = $group->getDepositAccount($depositAccountType, PaymentProcessor::HEARTLAND)) {
            throw new \RuntimeException('Cannot register to a group without deposit account.');
        }
        if (null == $registerToMerchantName = $depositAccount->getMerchantName()) {
            throw new \RuntimeException('Cannot register to a group without a merchant name.');
        }

        $existingDepositAccounts = $em->getRepository('RjDataBundle:DepositAccount')
            ->completeByPaymentAccount($paymentAccount);

        if (in_array($depositAccount, $existingDepositAccounts)) {
            // already associated
            return true;
        }

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
        $em->persist($paymentAccount);
        $em->flush();

        return true;
    }

    protected function registerTokenToAdditionalMerchant($merchantName, $registerToMerchantName, $token)
    {
        $request = new RegisterTokenToAdditionalMerchantRequest();
        $request->setToken($token);
        $request->getRegisterToMerchantCredential()->setMerchantName($registerToMerchantName);

        $paymentDetails = new PaymentDetails();
        $paymentDetails->setMerchantName($merchantName);
        $paymentDetails->setRequest($request);
        $captureRequest = new CaptureRequest($paymentDetails);

        if (method_exists($this, 'getContainer')) {
            /** @var Payment $payment */
            $payment = $this->getContainer()
                ->get('payum2')
                ->getPayment('heartland');
        } else {
            /** @var Payment $payment */
            $payment = $this->get('payum2')
                ->getPayment('heartland');
        }

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
