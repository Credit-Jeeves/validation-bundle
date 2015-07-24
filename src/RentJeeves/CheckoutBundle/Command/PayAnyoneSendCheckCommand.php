<?php
namespace RentJeeves\CheckoutBundle\Command;

use CreditJeeves\DataBundle\Entity\OrderPayDirect;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager\OrderStatusManagerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorAciPayAnyone;
use RentJeeves\DataBundle\Entity\OutboundTransaction;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PayAnyoneSendCheckCommand extends ContainerAwareCommand
{
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
        try {
            if ($this->getAciPayAnyonePaymentProcessor()->executeOrder($order)) {
                $output->writeln(sprintf('Check for order #%d has been sent successfully', $order->getId()));

                $this->getOrderStatusManager()->setSending($order);

                return 0;
            }
        } catch (\Exception $e) {
            $output->writeln(sprintf(
                '<error>Get exception "%s" with message: %s</error>',
                get_class($e),
                $e->getMessage()
            ));
        }

        /** @var OutboundTransaction $outboundTransaction */
        $outboundTransaction = $order->getDepositOutboundTransaction();
        $this->getOrderStatusManager()->setError($order);
        $output->writeln(sprintf(
            'Check for order #%d has not been sent successfully. Reason: %s',
            $order->getId(),
            $outboundTransaction->getMessage()
        ));

        return 1;
    }

    /**
     * @param int $orderId
     * @return OrderPayDirect
     */
    protected function getOrderById($orderId)
    {
        return $this->getContainer()->get('doctrine')->getManager()->find('DataBundle:OrderPayDirect', $orderId);
    }

    /**
     * @return PaymentProcessorAciPayAnyone
     */
    protected function getAciPayAnyonePaymentProcessor()
    {
        return $this->getContainer()->get('payment_processor.aci_pay_anyone');
    }

    /**
     * @return OrderStatusManagerInterface
     */
    protected function getOrderStatusManager()
    {
        return $this->getContainer()->get('payment_processor.order_status_manager');
    }
}
