<?php
namespace RentJeeves\CheckoutBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use RentJeeves\CheckoutBundle\Controller\Traits\AccountAssociate;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use Exception;

class RenameMerchantCommand extends ContainerAwareCommand
{
    use AccountAssociate;

    protected function configure()
    {
        $this
            ->setName('rj:merchant:rename')
            ->setDescription('Set a new merchant name for a group')
            ->addArgument(
                'from',
                InputArgument::REQUIRED,
                'Old merchant name'
            )
            ->addArgument(
                'to',
                InputArgument::REQUIRED,
                'New merchant name'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputInterface = $output;
        $oldMerchantName = $input->getArgument('from');
        $newMerchantName = $input->getArgument('to');

        $this->_changeMerchantName($oldMerchantName, $newMerchantName);
        $output->writeln(
            sprintf(
                "Merchant '%s' changed to '%s'",
                $oldMerchantName,
                $newMerchantName
            )
        );
    }

    /**
     * Get deposit account by merchant name or die
     *
     * @param string $merchantName merchant name
     *
     * @return DepositAccount
     */
    private function _getDepositAccount($merchantName)
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $depositAccount = $em->getRepository('RjDataBundle:DepositAccount')
            ->findOneByMerchantName($merchantName);

        if (!$depositAccount) {
            throw new Exception(
                "Could not find deposit account for merchant '$merchantName'"
            );
        }

        return $depositAccount;
    }

    /**
     * Get deposit account by merchant name or creates one if one does not
     * exist
     *
     * @param string $merchantName merchant name
     *
     * @return DepositAccount
     */
    private function _getOrCreateDepositAccount($merchantName)
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        // if the new one exists already, then get it
        $newDa = $em->getRepository('RjDataBundle:DepositAccount')
            ->findOneByMerchantName($merchantName);

        // if not, create the new deposit account
        if (!$newDa) {
            $newDa = new DepositAccount();
            $newDa->setMerchantName($merchantName);
            $newDa->setStatus(DepositAccountStatus::DA_COMPLETE);
            $em->persist($newDa);

            // save it now because we need it to have a database id
            $em->flush();
        }

        return $newDa;
    }

    /**
     * If possible, register a payment account to an additional merchant
     *
     * @param PaymentAccount $paymentAccount  payment account
     * @param DepositAccount $depositAccount  new deposit account
     * @param string         $oldMerchantName old merchant name
     * @param string         $newMerchantName new merchant name
     *
     * @return bool
     */
    private function _registerAdditionalMerchant(
        PaymentAccount $paymentAccount,
        DepositAccount $depositAccount,
        $oldMerchantName,
        $newMerchantName
    ) {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        // Skip if already registered to the new deposit account
        if ($paymentAccount->getDepositAccounts()->contains($depositAccount)) {
            $this->outputInterface->writeln('already registered');
            return false;
        }

        // Skip if payment account has no token
        if (!$paymentAccount->getToken()) {
            $this->outputInterface->writeln('no token');
            return false;
        }

        $this->registerTokenToAdditionalMerchant(
            $oldMerchantName,
            $newMerchantName,
            $paymentAccount->getToken()
        );

        $paymentAccount->addDepositAccount($depositAccount);
        $em->persist($paymentAccount);

        /**
          * flush each one because this could fail and we want to preserve
          * progress
          */
        $em->flush();

        $this->outputInterface->writeln('registered');
        return true;
    }

    /**
     * Loop through all the payment accounts of a deposit account and register
     * them to a new deposit account.
     *
     * @param DepositAccount $oldDepositAccount old deposit account
     * @param DepositAccount $newDepositAccount new deposit account
     * @param string         $oldMerchantName   old merchant name
     * @param string         $newMerchantName   new merchant name
     *
     * @return bool
     */
    private function _updatePaymentAccounts(
        DepositAccount $oldDepositAccount,
        DepositAccount $newDepositAccount,
        $oldMerchantName,
        $newMerchantName
    ) {
        // find all the payment accounts registered to the old deposit account
        $paymentAccountWorkingSet = $oldDepositAccount->getPaymentAccounts();
        $setSize = $paymentAccountWorkingSet->count();

        $this->outputInterface->writeln(
            sprintf("Checking %d payment accounts...", $setSize)
        );

        $count = 1;

        /**
          * Register each payment account with the new deposit account and
          * merchant
          */
        foreach ($paymentAccountWorkingSet as $paymentAccount) {
            $this->outputInterface->write(
                sprintf("Payment account %d of %d... ", $count, $setSize)
            );

            $this->_registerAdditionalMerchant(
                $paymentAccount,
                $newDepositAccount,
                $oldMerchantName,
                $newMerchantName
            );

            $count++;
        }

        return true;
    }

    /**
     * Manage the transition from one merchant name to another
     *
     * @param string $oldMerchantName old merchant name
     * @param string $newMerchantName new merchant name
     *
     * @return true
     */
    private function _changeMerchantName($oldMerchantName, $newMerchantName)
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $oldDa = $this->_getDepositAccount($oldMerchantName);
        $newDa = $this->_getOrCreateDepositAccount($newMerchantName);
        $this->_updatePaymentAccounts(
            $oldDa,
            $newDa,
            $oldMerchantName,
            $newMerchantName
        );

        /**
          * Remove the group from the old deposit account and add it to the
          * new one
          */
        $group = $oldDa->getGroup();
        $oldDa->setGroup(null);
        $newDa->setGroup($group);
        $em->flush();

        return true;
    }
}
