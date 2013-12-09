<?php

namespace RentJeeves\LandlordBundle\Report;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\Serializer\SerializationContext;
use \Exception;
use RentJeeves\LandlordBundle\Model\OrderReport;
use Symfony\Component\PropertyAccess\Exception\RuntimeException;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("report.order")
 */
class Order
{
    protected $em;

    protected $serializer;

    protected $begin;

    protected $end;

    protected $type;

    protected $propertyId;

    protected $isInitialized = false;

    /**
     * @InjectParams({
     *     "serializer"  = @Inject("jms_serializer"),
     *     "em"          = @Inject("doctrine.orm.default_entity_manager"),
     * })
     */
    public function __construct($serializer, $em)
    {
        $this->serializer = $serializer;
        $this->em         = $em;
    }

    public function getContentType()
    {
        if (!$this->isInitialized) {
            throw new RuntimeException('Not initialize data');
        }

        return sprintf(
            'text/%s',
            $this->type
        );
    }

    public function getFileName()
    {
        if (!$this->isInitialized) {
            throw new RuntimeException('Not initialize data');
        }
        return sprintf(
            'report_%s_and_%s.%s',
            $this->begin,
            $this->end,
            $this->type
        );
    }

    public function setupData($dataFromForm)
    {
        $this->begin = $dataFromForm['begin'];
        $this->end = $dataFromForm['end'];
        $this->propertyId = $dataFromForm['property']->getId();
        $this->type = $dataFromForm['type'];
        $this->isInitialized = true;
    }

    public function getData()
    {
        $orderRepository = $this->em->getRepository('DataBundle:Order');
        return $orderRepository->getOrdersForReport($this->propertyId, $this->begin, $this->end);
    }

    public function getReport($dataFromForm)
    {
        $this->setupData($dataFromForm);
        $ysiTran = new OrderReport();
        $ysiTran->setReceipt($this->getData());
        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups($this->type.'Report');
        $content = $this->serializer->serialize($ysiTran, $this->type, $context);

        return $content;
    }
}
