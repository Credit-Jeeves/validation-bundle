<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\ACI\AciCollectPay;

use JMS\DiExtraBundle\Annotation as DI;
use Payum\AciCollectPay\Request\ProfileRequest\CreateProfile;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException;
use RentJeeves\DataBundle\Entity\AciCollectPayContractBilling;
use RentJeeves\DataBundle\Entity\AciCollectPayUserProfile;
use RentJeeves\DataBundle\Entity\Contract;
use Payum\AciCollectPay\Model as RequestModel;

/**
 * @DI\Service("payment.aci_collect_pay.enrollment_manager", public=false)
 */
class EnrollmentManager extends AbstractManager
{
    /**
     * @param Contract $contract
     * @return int
     * @throws PaymentProcessorRuntimeException
     */
    public function createProfile(Contract $contract)
    {
        $user = $contract->getTenant();

        $this->logger->debug(
            sprintf('[ACI CollectPay Info]:Try to create new profile for user with id = "%d"', $user->getId())
        );

        $profile = new RequestModel\Profile();

        $profileUser = new RequestModel\SubModel\User();

        $profileUser->setUsername(md5($user->getId()));
        $profileUser->setPassword(md5($user->getEmail()));
        $profileUser->setName($user->getFullName());
        $profileUser->setEmail($user->getEmail());
        $profileUser->setPhone($user->getPhone());

        $profile->setUser($profileUser);

        if ($address = $user->getDefaultAddress()) {
            $profileAddress = new RequestModel\SubModel\Address();

            $profileAddress->setAddress1($address->getAddress());
            $profileAddress->setCity($address->getCity());
            $profileAddress->setState($address->getArea());
            $profileAddress->setPostalCode($address->getZip());
            $profileAddress->setCountryCode($address->getCountry());

            $profile->setAddress($profileAddress);
        }

        $billingAccount = $this->prepareBillingAccount($contract);

        $profile->setBillingAccount($billingAccount);

        $request = new CreateProfile($profile);

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
                '[ACI CollectPay Info]:Created new profile "%d" for user with id ="%d"',
                $request->getModel()->getProfileId(),
                $user->getId()
            )
        );

        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Added billing account to profile "%d" for contract with id ="%d"',
                $request->getModel()->getProfileId(),
                $contract->getId()
            )
        );

        $profile = new AciCollectPayUserProfile();
        $profile->setProfileId($request->getModel()->getProfileId());
        $profile->setUser($user);
        $user->setAciCollectPayProfile($profile);

        $this->em->persist($profile);
        $this->em->persist($user);

        $contractBilling = new AciCollectPayContractBilling();
        $contractBilling->setContract($contract);

        $contract->setAciCollectPayContractBilling($contractBilling);

        $this->em->persist($contract);
        $this->em->persist($contractBilling);

        $this->em->flush();

        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Saved profile "%d" for user with id ="%d"',
                $request->getModel()->getProfileId(),
                $user->getId()
            )
        );

        $this->logger->debug(
            sprintf(
                '[ACI CollectPay Info]:Saved billing account for profile "%d" for contract with id ="%d"',
                $request->getModel()->getProfileId(),
                $contract->getId()
            )
        );

        return $request->getModel()->getProfileId();
    }
}
