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

    protected $property;

    protected $isInitialized = false;

    protected $propertyId;

    protected $arAccountId;

    protected $accountId;


    protected $mapping = array(
        'xml' => array(
            'content-type' => 'text/xml',
            'serializer'   => 'xml',
            'group'        => 'xmlReport'
        ),
        'csv' => array(
            'content-type' => 'text/csv',
            'serializer'   => 'csv',
            'group'        => 'csvReport'
        )
    );

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
            throw new RuntimeException('Report data is not initialized');
        }

        return $this->mapping[$this->type]['content-type'];
    }

    public function getFileName()
    {
        if (!$this->isInitialized) {
            throw new RuntimeException('Report data is not initialized');
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
        $this->property = $dataFromForm['property'];
        $this->type = $dataFromForm['type'];
        $this->propertyId = $dataFromForm['propertyId'];
        $this->arAccountId = $dataFromForm['arAccountId'];
        $this->accountId = $dataFromForm['accountId'];

        if (!isset($this->mapping[$this->type])) {
            throw new RuntimeException('Report type is invalid');
        }
        $this->isInitialized = true;
    }

    public function getData()
    {
        $orderRepository = $this->em->getRepository('DataBundle:Order');
        return $orderRepository->getOrdersForReport($this->property->getId(), $this->begin, $this->end);
    }

    public function getReport($dataFromForm)
    {
        $this->setupData($dataFromForm);

        $group = $this->mapping[$this->type]['group'];
        $serializer  = $this->mapping[$this->type]['serializer'];

        $ysiTran = new OrderReport();
        $ysiTran->setReceipt($this->getData());
        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups($group);
        $content = $this->serializer->serialize($ysiTran, $serializer, $context);

        return $content;
    }

    /**
     * @return mixed
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @return mixed
     */
    public function getArAccountId()
    {
        return $this->arAccountId;
    }

    /**
     * @return mixed
     */
    public function getPropertyId()
    {
        return $this->propertyId;
    }
}
