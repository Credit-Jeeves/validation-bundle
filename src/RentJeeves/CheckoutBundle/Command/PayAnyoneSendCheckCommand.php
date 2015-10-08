<?php
namespace RentJeeves\CheckoutBundle\Command;

use CreditJeeves\DataBundle\Entity\OrderPayDirect;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\CheckSender;

class PayAnyoneSendCheckCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('payment:pay-anyone:send-check')
            ->addOption('jms-job-id', null, InputOption::VALUE_OPTIONAL, 'ID of job')
            ->addArgument('order-id', InputArgument::REQUIRED, 'ID of order')
            ->setDescription('Send check when inbound transaction is complete');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (false == $order = $this->getOrderById($input->getArgument('order-id'))) {
            throw new \InvalidArgumentException(sprintf('Order with id#%s not found', $input->getArgument('order-id')));
        }

        $this->getAciPayAnyoneCheckSender()->send($order);
    }

    /**
     * @param int $orderId
     *
     * @return OrderPayDirect
     */
    protected function getOrderById($orderId)
    {
        return $this->getContainer()->get('doctrine')->getManager()->find('DataBundle:OrderPayDirect', $orderId);
    }

    /**
     * @return CheckSender
     */
    protected function getAciPayAnyoneCheckSender()
    {
        return $this->getContainer()->get('payment_processor.aci.pay_anyone.check_sender');
    }
}
