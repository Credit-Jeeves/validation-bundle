<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay;

use Payum\AciCollectPay\Model as RequestModel;
use Payum\AciCollectPay\Request\ProfileRequest\AddBilling;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException;
use RentJeeves\DataBundle\Entity\AciCollectPayContractBilling;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\DepositAccountType;

class BillingAccountManager extends AbstractManager
{
    /**
     * @param  int $profileId
     * @param  Contract $contract
     * @param  string $depositAccountType
     * @throws \Exception
     */
    public function addBillingAccount($profileId, Contract $contract, $depositAccountType = DepositAccountType::RENT)
    {
        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Try to add billing account to profile "%d" for contract with id = "%d"',
                $profileId,
                $contract->getId()
            )
        );

        $profile = new RequestModel\Profile();

        $profile->setProfileId($profileId);

        $billingAccount = $this->prepareBillingAccount($contract, $depositAccountType);

        $profile->setBillingAccount($billingAccount);

        $request = new AddBilling($profile);

        try {
            $this->paymentProcessor->execute($request);
        } catch (\Exception $e) {
            $this->logger->alert(sprintf('[ACI CollectPay Critical Error]:%s', $e->getMessage()));
            throw $e;
        }

        if (!$request->getIsSuccessful()) {
            $this->logger->alert(sprintf('[ACI CollectPay Error]:%s', $request->getMessages()));
            throw new PaymentProcessorRuntimeException(self::removeDebugInformation($request->getMessages()));
        }

        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Added billing account to profile "%d" for contract with id = "%d"',
                $request->getModel()->getProfileId(),
                $contract->getId()
            )
        );

        $contractBilling = new AciCollectPayContractBilling();
        $contractBilling->setContract($contract);
        $contractBilling->setDivisionId($billingAccount->getBusinessId());

        $contract->addAciCollectPayContractBilling($contractBilling);

        $this->em->persist($contractBilling);

        $this->em->flush();

        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Saved billing account for profile "%d" for contract with id = "%d"',
                $request->getModel()->getProfileId(),
                $contract->getId()
            )
        );
    }
}
