<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay;

use Payum\AciCollectPay\Model as RequestModel;
use Payum\AciCollectPay\Request\ProfileRequest\AddBilling;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException;
use RentJeeves\DataBundle\Entity\AciCollectPayProfileBilling;
use RentJeeves\DataBundle\Entity\AciCollectPayUserProfile;
use RentJeeves\DataBundle\Entity\DepositAccount;

class BillingAccountManager extends AbstractManager
{
    /**
     * @param AciCollectPayUserProfile $profile
     * @param DepositAccount $depositAccount
     * @throws \Exception
     */
    public function addBillingAccount(AciCollectPayUserProfile $userProfile, DepositAccount $depositAccount)
    {
        if (!$userProfile->hasBillingAccountForDivisionId($depositAccount->getMerchantName())) {
            $this->logger->debug(
                sprintf(
                    '[ACI CollectPay Info]:Try to add billing account to profile "%d" for deposit account id = "%d"',
                    $userProfile->getProfileId(),
                    $depositAccount->getId()
                )
            );

            $profile = new RequestModel\Profile();

            $profile->setProfileId($userProfile->getProfileId());

            $billingAccount = $this->prepareBillingAccount($userProfile->getUser(), $depositAccount);

            $profile->setBillingAccount($billingAccount);

            $request = new AddBilling($profile);

            try {
                $this->paymentProcessor->execute($request);
            } catch (\Exception $e) {
                $this->logger->alert(
                    sprintf(
                        '[ACI CollectPay BillingAccount Exception]:User(%s):%s',
                        $userProfile->getUser()->getId(),
                        $e->getMessage()
                    )
                );
                throw $e;
            }

            if (!$request->getIsSuccessful()) {
                $this->logger->alert(
                    sprintf(
                        '[ACI CollectPay BillingAccount Error]:User(%s):%s',
                        $userProfile->getUser()->getId(),
                        $request->getMessages()
                    )
                );
                throw new PaymentProcessorRuntimeException(self::removeDebugInformation($request->getMessages()));
            }

            $this->logger->debug(
                sprintf(
                    '[ACI CollectPay Info]:Added billing account to profile "%d" for deposit account id = "%d"',
                    $request->getModel()->getProfileId(),
                    $depositAccount->getId()
                )
            );

            $profileBilling = new AciCollectPayProfileBilling();
            $profileBilling->setProfile($userProfile);
            $profileBilling->setDivisionId($billingAccount->getBusinessId());
            $profileBilling->setBillingAccountNumber(
                $this->getUserBillingAccountNumber($userProfile->getUser(), $depositAccount)
            );

            $userProfile->addAciCollectPayProfileBilling($profileBilling);

            $this->em->persist($profileBilling);
            $this->em->flush();

            $this->logger->debug(
                sprintf(
                    '[ACI CollectPay Info]:Saved billing account for profile "%d" for deposit account id = "%d"',
                    $request->getModel()->getProfileId(),
                    $depositAccount->getId()
                )
            );
        } else {
            $this->logger->debug(sprintf(
                '[ACI CollectPay Info]:Billing account for profile "%d" and deposit account id = "%d" already exists',
                $userProfile->getProfileId(),
                $depositAccount->getId()
            ));
        }
    }
}
