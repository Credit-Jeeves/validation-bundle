<?php
namespace RentJeeves\DataBundle\EventListener;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OperationType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\DependencyInjection\ContainerInterface;
use DateTime;

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
     * Three main goals for this method:
     * 1. Set paidTo for contract
     * 2. Set daysLate for order
     * 3. Mark tenant as ready for charge
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof Order) {
            $entity->countDaysLate();
            $this->chargePartner($entity, $eventArgs->getEntityManager());
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
                            $this->container->get('project.mailer')->sendOrderCancelToTenant($entity);
                            $this->container->get('project.mailer')->sendOrderCancelToLandlord($entity);
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

    private function chargePartner(Order $order, EntityManager $em)
    {
        $operation = $order->getOperations()->last();
        if ($operation && $operation->getType() == OperationType::RENT) {
            /** @var User $user */
            $user = $order->getUser();
            $countOrders = count($user->getOrders());
            $partnerCode = $user->getPartnerCode();

            if ($countOrders == 0 && $partnerCode) {
                $partnerCode->setFirstPaymentDate(new DateTime());
            }
        }
    }
}
