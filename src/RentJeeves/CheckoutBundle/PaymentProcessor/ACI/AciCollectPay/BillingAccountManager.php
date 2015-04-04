<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\ACI\AciCollectPay;

use JMS\DiExtraBundle\Annotation as DI;
use Payum\AciCollectPay\Request\ProfileRequest\AddBilling;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException;
use RentJeeves\DataBundle\Entity\AciCollectPayContractBilling;
use RentJeeves\DataBundle\Entity\AciCollectPayContractBillingRepository;
use RentJeeves\DataBundle\Entity\Contract;
use Payum\AciCollectPay\Model as RequestModel;

/**
 * @DI\Service("payment.aci_collect_pay.billing_account_manager")
 */
class BillingAccountManager extends AbstractManager
{
    /**
     * @param Contract $contract
     * @return bool
     */
    public function hasBillingAccount(Contract $contract)
    {
        /** @var AciCollectPayContractBillingRepository $repo */
        $repo = $this->em->getRepository('RjDataBundle:AciCollectPayContractBilling');

        return !!$repo->findByContract($contract);
    }

    /**
     * @param $profileId
     * @param Contract $contract
     */
    public function addBillingAccount($profileId, Contract $contract)
    {
        $group = $contract->getGroup();

        $profile = new RequestModel\Profile();

        $profile->setProfileId($profileId);

        $billingAccount = new RequestModel\SubModel\BillingAccount();

        $billingAccount->setAccountNumber($contract->getId());
        $billingAccount->setBusinessId($group->getAciCollectPaySettings()->getBusinessId());
        $billingAccount->setHoldername($group->getAciCollectPaySettings()->getHolderName());
        $billingAccount->setNickname($contract->getGroup()->getName());

        // TODO Need Implement this
//        $billingAccountAddress = new RequestModel\SubModel\Address();
//
//        $billingAccountAddress->setAddress1($group->getStreetAddress1());
//        $billingAccountAddress->setAddress2($group->getStreetAddress2());
//        $billingAccountAddress->setCity($group->getCity());
//        $billingAccountAddress->setPostalCode($group->getZip());
//        $billingAccountAddress->setState($group->getState());
//
//        $billingAccount->setAddress($billingAccountAddress);

        $profile->setBillingAccount($billingAccount);

        $request = new AddBilling($profile);

        $this->paymentProcessor->execute($request);

        if (!$request->getIsSuccessful()) {
            throw new PaymentProcessorRuntimeException($request->getMessages());
        }

        $contractBilling = new AciCollectPayContractBilling();
        $contractBilling->setContract($contract);

        $this->em->persist($contractBilling);

        $this->em->flush();
    }
}
