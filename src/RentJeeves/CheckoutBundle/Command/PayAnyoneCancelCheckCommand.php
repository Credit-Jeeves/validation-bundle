<?php
namespace RentJeeves\CheckoutBundle\Command;

use CreditJeeves\DataBundle\Entity\Order;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorAciPayAnyone;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PayAnyoneCancelCheckCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('payment:pay-anyone:cancel-check')
            ->addOption('jms-job-id', null, InputOption::VALUE_REQUIRED, 'ID of job')
            ->addArgument('order-id', InputArgument::REQUIRED, 'ID of order')
            ->setDescription('Send check when inbound transaction is cancel');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (false == $order = $this->getOrderById($input->getArgument('order-id'))) {
            throw new \InvalidArgumentException(sprintf('Order with id#%s not found', $input->getArgument('order-id')));
        }
        $this->getAciPayAnyonePaymentProcessor()->cancelOrder($order);
    }

    /**
     * @param int $orderId
     * @return Order
     */
    protected function getOrderById($orderId)
    {
        return $this->getContainer()->get('doctrine')->getManager()->find('DataBundle:Order', $orderId);
    }

    /**
     * @return PaymentProcessorAciPayAnyone
     */
    protected function getAciPayAnyonePaymentProcessor()
    {
        return $this->getContainer()->get('payment_processor.aci.pay_anyone');
    }
}
