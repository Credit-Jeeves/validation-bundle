<?php
namespace RentJeeves\CheckoutBundle\Controller\Traits;

use CreditJeeves\DataBundle\Entity\Group;
use Payum\Heartland\Soap\Base\RegisterTokenToAdditionalMerchantRequest;
use Payum\Payment;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\Heartland as PaymentDetails;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RuntimeException;

/**
 * @author Stephen Crosby <stephen@brandedcrate.com>
 */
trait AccountAssociate
{
    /**
     * Ensure there is an association between the given paymentAccount and
     * group. If there isn't, form the association by requesting
     * registerTokenToAdditionalMerchant from heartland, then creating a DB
     * association between the paymentAccount and the group's depositAccount.
     *
     * @param PaymentAccount $paymentAccount
     * @param Group $group
     * @return Boolean
     */
    protected function ensureAccountAssociation(PaymentAccount $paymentAccount, Group $group)
    {
        $em = $this->getDoctrine()->getManager();
        $depositAccount = $group->getDepositAccount();
        $registerToMerchantName = $group->getMerchantName();

        if ($registerToMerchantName == null) {
            throw new RuntimeException('Cannot register to a group without a ' .
                'merchant name.');
        }

        $existingDepositAccounts = $em->createQueryBuilder()
            ->from('RjDataBundle:DepositAccount', 'da')
            ->select('da')
            ->join('da.paymentAccounts', 'pa')
            ->join('da.group', 'g')
            ->where('da.status = :status')
            ->andWhere('da.id <> :deposit_account_id')
            ->andWhere('pa.id = :payment_account_id')
            ->setParameter('status', DepositAccountStatus::DA_COMPLETE)
            ->setParameter('deposit_account_id', $depositAccount->getId())
            ->setParameter('payment_account_id', $paymentAccount->getId())
            ->getQuery()
            ->execute();

        if (empty($existingDepositAccounts)) {
            throw new RuntimeException('Registering to another deposit ' .
                'account only works when there is at least one existing ' .
                'association.');
        }

        // any previously registered group will work
        $merchantName = $existingDepositAccounts[0]->getGroup()->getMerchantName();
        $token = $paymentAccount->getToken();

        $this->registerTokenToAdditionalMerchant($merchantName, $registerToMerchantName, $token);

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
        $request->getRegisterToMerchantCredential()->setMerchantName($merchantName);

        $paymentDetails = new PaymentDetails();
        $paymentDetails->setMerchantName($merchantName);
        $paymentDetails->setRequest($request);
        $captureRequest = new CaptureRequest($paymentDetails);

        /** @var Payment $payment */
        $payment = $this->get('payum')->getPayment('heartland');
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
