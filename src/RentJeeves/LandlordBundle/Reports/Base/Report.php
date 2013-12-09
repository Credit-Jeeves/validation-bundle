<?php

namespace RentJeeves\LandlordBundle\Reports\Base;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\Serializer\SerializationContext;
use \Exception;
use RentJeeves\LandlordBundle\Model\Reports\BaseOrderReport\YsiTran;
use RentJeeves\LandlordBundle\Reports\ReportInterface;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("base.report")
 */
class Report implements ReportInterface
{
    protected $em;

    protected $serializer;

    protected $begin;

    protected $end;

    protected $type;

    protected $propertyId;

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
        return 'text/' . $this->type;
    }

    public function getFileName()
    {
        return 'report_' . $this->getBegin() . '_and_' . $this->getEnd() . '.' . $this->getType();
    }

    public function setupData($dataFromForm)
    {
        $this->setBegin($dataFromForm['begin']);
        $this->setEnd($dataFromForm['end']);
        $this->setPropertyId($dataFromForm['property']->getId());
        $this->setType($dataFromForm['type']);
    }

    public function getOrders()
    {
        $orderRepository = $this->em->getRepository('DataBundle:Order');
        return $orderRepository->getOrdersForReport($this->getPropertyId(), $this->getBegin(), $this->getEnd());
    }

    public function getReport($dataFromForm = null)
    {
        if (!is_null($dataFromForm)) {
            $this->setupData($dataFromForm);
        }

        switch ($this->getType()) {
            case 'xml':
                $ysiTran = new YsiTran();
                $ysiTran->setReceipt($this->getOrders());
                $context = new SerializationContext();
                $context->setSerializeNull(true);
                $context->setGroups('xmlBaseReport');
                $content = $this->serializer->serialize($ysiTran, 'xml', $context);
                break;
            case 'csv':
                $context = new SerializationContext();
                $context->setSerializeNull(true);
                $context->setGroups('csvBaseReportCsv');
                $content = $this->serializer->serialize($this->getOrders(), 'csv', $context);
                break;
            default:
                throw new Exception("We does not have logic for this type report:".$this->getType());
        }
        return $content;
    }

    /**
     * @param string $begin
     */
    public function setBegin($begin)
    {
        $this->begin = $begin;
    }

    /**
     * @return string
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * @param object $em
     */
    public function setEm($em)
    {
        $this->em = $em;
    }

    /**
     * @return object
     */
    public function getEm()
    {
        return $this->em;
    }

    /**
     * @param string $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * @return string
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param int $propertyId
     */
    public function setPropertyId($propertyId)
    {
        $this->propertyId = $propertyId;
    }

    /**
     * @return int
     */
    public function getPropertyId()
    {
        return $this->propertyId;
    }

    /**
     * @param object $serializer
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @return object
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
