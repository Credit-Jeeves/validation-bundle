<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\ACI\AciCollectPay;

use JMS\DiExtraBundle\Annotation as DI;
use Payum\AciCollectPay\Request\ProfileRequest\AddBilling;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException;
use RentJeeves\DataBundle\Entity\AciCollectPayContractBilling;
use RentJeeves\DataBundle\Entity\Contract;
use Payum\AciCollectPay\Model as RequestModel;

/**
 * @DI\Service("payment.aci_collect_pay.billing_account_manager", public=false)
 */
class BillingAccountManager extends AbstractManager
{
    /**
     * @param int $profileId
     * @param Contract $contract
     * @throws PaymentProcessorRuntimeException
     */
    public function addBillingAccount($profileId, Contract $contract)
    {
        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Try to add billing account to profile "%d" for contract with id ="%d"',
                $profileId,
                $contract->getId()
            )
        );

        $profile = new RequestModel\Profile();

        $profile->setProfileId($profileId);

        $billingAccount = $this->prepareBillingAccount($contract);

        $profile->setBillingAccount($billingAccount);

        $request = new AddBilling($profile);

        try {
            $this->paymentProcessor->execute($request);
        } catch(\Exception $e) {
            $this->logger->err(sprintf('[ACI CollectPay Critical Error]:%s', $e->getMessage()));
            throw new $e;
        }

        if (!$request->getIsSuccessful()) {
            $this->logger->err(sprintf('[ACI CollectPay Error]:%s', $request->getMessages()));
            throw new PaymentProcessorRuntimeException($request->getMessages());
        }

        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Added billing account to profile "%d" for contract with id ="%d"',
                $request->getModel()->getProfileId(),
                $contract->getId()
            )
        );

        $contractBilling = new AciCollectPayContractBilling();
        $contractBilling->setContract($contract);

        $contract->setAciCollectPayContractBilling($contractBilling);

        $this->em->persist($contract);
        $this->em->persist($contractBilling);

        $this->em->flush();

        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Saved billing account for profile "%d" for contract with id ="%d"',
                $request->getModel()->getProfileId(),
                $contract->getId()
            )
        );
    }
}
