<?php
namespace RentJeeves\DataBundle\EventListener;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OperationType;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OrderListener
{
    /**
     * 
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Two main goals for this method:
     * 1. Set paidTo for contract
     * 2. Set daysLate for order
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof Order) {
            $entity->countDaysLate();
        }
    }

    /**
     * Why we need to use preUpdate event?
     * Because Order always(!!!) is created with status "NEWONE"
     * It will be changed after attempt of payment
     * 
     * @param LifecycleEventArgs $eventArgs
     */
    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof Order) {
            $status = $entity->getStatus();
            $status = $entity->getStatus();
            if ($status == OrderStatus::COMPLETE) {
                $entity->checkOrderProperties();
            }
        }
    }
    

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof Order) {
            $type = OperationType::RENT;
            $operation = $entity->getOperations()->last();
            $type = $operation ? $operation->getType(): $type;
            switch ($type) {
                case OperationType::RENT:
                    $status = $entity->getStatus();
                    switch ($status) {
                        case OrderStatus::COMPLETE:
                            $this->container->get('project.mailer')->sendRentReceipt($entity);
                            break;
                        case OrderStatus::ERROR:
                            $this->container->get('project.mailer')->sendRentError($entity);
                            break;
                        case OrderStatus::REFUNDED:
                        case OrderStatus::CANCELLED:
                        case OrderStatus::RETURNED:
                            $this->container->get('project.mailer')->sendOrderCancel($entity);
                            break;
                    }
                    break;
                case OperationType::REPORT:
                    $status = $entity->getStatus();
                    switch ($status) {
                        case OrderStatus::COMPLETE:
                            $this->container->get('project.mailer')->sendReportReceipt($entity);
                            break;
                    }
                    break;
            }
        }
    }
}
