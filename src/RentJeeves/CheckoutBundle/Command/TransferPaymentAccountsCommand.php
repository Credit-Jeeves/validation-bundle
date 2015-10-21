<?php
namespace RentJeeves\CheckoutBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use Exception;

class TransferPaymentAccountsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $description = 'Registers payment accounts belonging to one ' .
            'deposit account with another deposit account';

        $this
            ->setName('rj:transfer_payment_accounts')
            ->setDescription($description)
            ->addArgument(
                'from',
                InputArgument::REQUIRED,
                'Old deposit account id'
            )
            ->addArgument(
                'to',
                InputArgument::REQUIRED,
                'New deposit account id'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputInterface = $output;
        $oldDepositAccountId = $input->getArgument('from');
        $newDepositAccountId = $input->getArgument('to');

        $oldDepositAccount = $this->getDepositAccount($oldDepositAccountId);
        $newDepositAccount = $this->getDepositAccount($newDepositAccountId);

        $this->updatePaymentAccounts(
            $oldDepositAccount,
            $newDepositAccount
        );

        $finishMessage = "Payment accounts from deposit account '%s' " .
            "registered to deposit account '%s'";

        $output->writeln(
            sprintf(
                $finishMessage,
                $oldDepositAccount->getId(),
                $newDepositAccount->getid()
            )
        );
    }

    /**
     * Get deposit account by id, make sure it exists, or die
     *
     * @param string $depositAccountId deposit account id
     *
     * @return DepositAccount
     */
    private function getDepositAccount($depositAccountId)
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $depositAccount = $em->getRepository('RjDataBundle:DepositAccount')
            ->findOneById($depositAccountId);

        if (!$depositAccount) {
            throw new Exception(
                "Could not find deposit account with id '$depositAccountId'"
            );
        }

        return $depositAccount;
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
    private function registerAdditionalMerchant(
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

        try {
            $this->registerTokenToAdditionalMerchant(
                $oldMerchantName,
                $newMerchantName,
                $paymentAccount->getToken()
            );
        } catch (Exception $e) {
            $this->outputInterface->writeln('failed: ' . $e->getMessage());
            return false;
        }

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
     *
     * @return bool
     */
    private function updatePaymentAccounts(
        DepositAccount $oldDepositAccount,
        DepositAccount $newDepositAccount
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

            $this->registerAdditionalMerchant(
                $paymentAccount,
                $newDepositAccount,
                $oldDepositAccount->getMerchantName(),
                $newDepositAccount->getMerchantName()
            );

            $count++;
        }

        return true;
    }
}
