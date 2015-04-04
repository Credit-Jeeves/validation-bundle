<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\ACI\AciCollectPay;

use CreditJeeves\DataBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Payum\AciCollectPay\Request\ProfileRequest\CreateProfile;
use Payum\AciCollectPay\Request\ProfileRequest\DeleteProfile;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException;
use RentJeeves\DataBundle\Entity\AciCollectPayContractBilling;
use RentJeeves\DataBundle\Entity\AciCollectPayUserProfile;
use RentJeeves\DataBundle\Entity\Contract;
use Payum\AciCollectPay\Model as RequestModel;

/**
 * @DI\Service("payment.aci_collect_pay.enrollment_manager")
 */
class EnrollmentManager extends AbstractManager
{
    /**
     * @param User $user
     * @return int|void
     */
    public function getProfileId(User $user)
    {
        if ($user->getAciCollectPayProfile()) {
            return $user->getAciCollectPayProfile()->getProfileId();
        }
    }

    /**
     * @param Contract $contract
     * @return int
     * @throws \Exception
     * @throws \Payum\Core\Reply\ReplyInterface
     */
    public function createProfile(Contract $contract)
    {
        $user = $contract->getTenant();
        $group = $contract->getGroup();

        $profile = new RequestModel\Profile();

        $profileUser = new RequestModel\SubModel\User();

        $profileUser->setUsername($this->getUsername($user->getEmail()));
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

        $billingAccount = new RequestModel\SubModel\BillingAccount();

        $billingAccount->setAccountNumber($contract->getId());
        $billingAccount->setBusinessId($group->getAciCollectPaySettings()->getBusinessId());
        $billingAccount->setHoldername($group->getAciCollectPaySettings()->getHolderName());
        $billingAccount->setNickname($group->getName());

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

        $request = new CreateProfile($profile);

        $this->paymentProcessor->execute($request);

        if (!$request->getIsSuccessful()) {
            throw new PaymentProcessorRuntimeException($request->getMessages());
        }

        $profile = new AciCollectPayUserProfile();
        $profile->setProfileId($request->getModel()->getProfileId());
        $profile->setUser($user);
        $user->setAciCollectPayProfile($profile);

        $this->em->persist($profile);
        $this->em->persist($user);

        $contractBilling = new AciCollectPayContractBilling();
        $contractBilling->setContract($contract);

        $this->em->persist($contractBilling);

        $this->em->flush();

        return $request->getModel()->getProfileId();
    }

    /**
     * @param $profileId
     * @throws PaymentProcessorRuntimeException
     */
    public function deleteProfile($profileId)
    {
        $profile = new RequestModel\Profile();

        $profile->setProfileId($profileId);

        $request = new DeleteProfile($profile);

        $this->paymentProcessor->execute($request);

        if (!$request->getIsSuccessful()) {
            throw new PaymentProcessorRuntimeException($request->getMessages());
        }
    }

    /**
     * @param $email
     * @return string
     */
    protected function getUsername($email)
    {
        return preg_replace('/[^0-9a-zA-Z]/', '', $email);
    }
}
