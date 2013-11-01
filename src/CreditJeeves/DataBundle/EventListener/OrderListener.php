<?php
namespace CreditJeeves\DataBundle\EventListener;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OperationType;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;

class OrderListener
{
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof Order) {
            $type = OperationType::REPORT;
            $operation = $entity->getOperations()->last();
            $type = $operation ? $operation->getType(): $type;
            switch ($type) {
//                 case OperationType::RENT:
//                     $status = $entity->getStatus();
//                     switch ($status) {
//                         case OrderStatus::COMPLETE:
//                             $this->container->get('project.mailer')->sendOrderReceipt($entity);
//                             break;
//                         case OrderStatus::ERROR:
//                             $this->container->get('project.mailer')->sendOrderError($entity);
//                             break;
//                     }
//                     break;
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
