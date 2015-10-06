<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\User;
use Payum\AciCollectPay\Model as RequestModel;
use Payum\AciCollectPay\Request\ProfileRequest\CreateProfile;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException;
use RentJeeves\DataBundle\Entity\AciCollectPayContractBilling;
use RentJeeves\DataBundle\Entity\AciCollectPayGroupProfile;
use RentJeeves\DataBundle\Entity\AciCollectPayProfileBilling;
use RentJeeves\DataBundle\Entity\AciCollectPayUserProfile;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;

class EnrollmentManager extends AbstractManager
{
    /**
     * @param  Tenant $user
     * @param  DepositAccount $depositAccount
     * @return AciCollectPayUserProfile
     * @throws \Exception
     */
    public function createUserProfile(Tenant $user, DepositAccount $depositAccount)
    {
        if (!($userProfile = $user->getAciCollectPayProfile())) {
            $this->logger->debug(
                sprintf('[ACI CollectPay Info]:Try to create new profile for user with id = "%d"', $user->getId())
            );

            $aciProfile = $this->prepareUserProfile($user);

            $billingAccount = $this->prepareBillingAccount($user, $depositAccount);

            $aciProfile->setBillingAccount($billingAccount);

            $aciProfile = $this->executeRequest($aciProfile);

            $this->logger->debug(
                sprintf(
                    '[ACI CollectPay Info]:Created new profile "%d" for user with id = "%d"',
                    $aciProfile->getProfileId(),
                    $user->getId()
                )
            );

            $this->logger->debug(
                sprintf(
                    '[ACI CollectPay Info]:Added billing account to profile "%d" for deposit account id = "%d"',
                    $aciProfile->getProfileId(),
                    $depositAccount->getId()
                )
            );

            $userProfile = new AciCollectPayUserProfile();
            $userProfile->setProfileId($aciProfile->getProfileId());
            $userProfile->setUser($user);
            $user->setAciCollectPayProfile($userProfile);

            $this->em->persist($userProfile);

            $profileBilling = new AciCollectPayProfileBilling();
            $profileBilling->setProfile($userProfile);
            $profileBilling->setDivisionId($depositAccount->getMerchantName());
            $profileBilling->setBillingAccountNumber($billingAccount->getAccountNumber());
            $userProfile->addAciCollectPayProfileBilling($profileBilling);

            $this->em->persist($profileBilling);

            $this->em->flush();

            $this->logger->debug(
                sprintf(
                    '[ACI CollectPay Info]:Saved profile "%d" for user with id = "%d"',
                    $aciProfile->getProfileId(),
                    $user->getId()
                )
            );

            $this->logger->debug(
                sprintf(
                    '[ACI CollectPay Info]:Saved billing account for profile "%d" for deposit account id = "%d"',
                    $aciProfile->getProfileId(),
                    $depositAccount->getId()
                )
            );
        } else {
            $this->logger->debug(
                sprintf('[ACI CollectPay Info]:User profile for user id = "%d" already exists', $user->getId())
            );
        }

        return $userProfile;
    }

    /**
     * @param Group $group
     * @param Landlord $landlord
     * @return AciCollectPayGroupProfile
     * @throws PaymentProcessorRuntimeException
     */
    public function createGroupProfile(Group $group, Landlord $landlord)
    {
        if (!($groupProfile = $group->getAciCollectPayProfile())) {
            $this->logger->debug(
                sprintf(
                    '[ACI CollectPay Info]:Try to create new profile for group "%s" using landlord with id = "%d"',
                    $group->getName(),
                    $landlord->getId()
                )
            );

            $profile = $this->prepareGroupProfile($landlord, $group);

            $billingAccount = $this->prepareGroupBillingAccount($group);

            $profile->setBillingAccount($billingAccount);

            $profile = $this->executeRequest($profile);

            $this->logger->debug(
                sprintf(
                    '[ACI CollectPay Info]:Created new profile "%d" for group "%s" using landlord with id = "%d"',
                    $profile->getProfileId(),
                    $group->getName(),
                    $landlord->getId()
                )
            );

            $this->logger->debug(
                sprintf(
                    '[ACI CollectPay Info]:Added billing account to profile "%d" for group "%s"',
                    $profile->getProfileId(),
                    $group->getName()
                )
            );

            $groupProfile = new AciCollectPayGroupProfile();
            $groupProfile->setProfileId($profile->getProfileId());
            $groupProfile->setGroup($group);
            $groupProfile->setBillingAccountNumber($billingAccount->getAccountNumber());
            $group->setAciCollectPayProfile($groupProfile);

            $this->em->persist($groupProfile);

            $this->em->flush();

            $this->logger->debug(
                sprintf(
                    '[ACI CollectPay Info]:Saved profile "%d" for group "%s"',
                    $profile->getProfileId(),
                    $group->getName()
                )
            );
        } else {
            $this->logger->debug(
                sprintf(
                    '[ACI CollectPay Info]:Group profile for group "%s" and landlord id = "%d" already exists',
                    $group->getName(),
                    $landlord->getId()
                )
            );
        }

        return $groupProfile;
    }

    /**
     * @param RequestModel\Profile $profile
     * @return RequestModel\Profile
     */
    protected function executeRequest(RequestModel\Profile $profile)
    {
        $request = new CreateProfile($profile);

        try {
            $this->paymentProcessor->execute($request);
        } catch (\Exception $e) {
            $this->logger->alert(sprintf('[ACI CollectPay Critical Error]:%s', $e->getMessage()));
            throw $e;
        }

        if (!$request->getIsSuccessful()) {
            $this->logger->alert(sprintf('[ACI CollectPay Error]:%s', $request->getMessages()));
            throw new PaymentProcessorRuntimeException($request->getMessages());
        }

        return $request->getModel();
    }

    /**
     * @param User $user
     * @return RequestModel\Profile
     */
    protected function prepareUserProfile(User $user)
    {
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

        return $profile;
    }

    /**
     * @param User $user
     * @param Group $group
     * @return RequestModel\Profile
     */
    protected function prepareGroupProfile(User $user, Group $group)
    {
        $profile = $this->prepareUserProfile($user);

        $groupUsername = md5('G' . $group->getId());
        $profile->getUser()->setUsername($groupUsername);

        return $profile;
    }

    /**
     * @param Group $group
     * @return RequestModel\SubModel\BillingAccount
     */
    protected function prepareGroupBillingAccount(Group $group)
    {
        $billingAccount = new RequestModel\SubModel\BillingAccount();

        $billingAccount->setAccountNumber($this->getGroupBillingAccountNumber($group, $this->defaultBusinessId));
        $billingAccount->setBusinessId($this->defaultBusinessId);
        $billingAccount->setHoldername($group->getName());
        $billingAccount->setNickname($group->getName());

        $billingAccountAddress = new RequestModel\SubModel\Address();

        $billingAccountAddress->setAddress1((string) $group->getStreetAddress1());
        $billingAccountAddress->setAddress2((string) $group->getStreetAddress2());
        $billingAccountAddress->setCity((string) $group->getCity());
        $billingAccountAddress->setPostalCode((string) $group->getZip());
        $billingAccountAddress->setState((string) $group->getState());
        $billingAccountAddress->setCountryCode((string) $group->getCountry());

        $billingAccount->setAddress($billingAccountAddress);

        return $billingAccount;
    }
}
