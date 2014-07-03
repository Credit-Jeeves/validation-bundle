<?php

namespace RentJeeves\LandlordBundle\Accounting;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\Serializer\SerializationContext;
use \Exception;
use RentJeeves\LandlordBundle\Model\OrderReport;
use Symfony\Component\PropertyAccess\Exception\RuntimeException;
use DateTime;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("accounting.export")
 */
class Export
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

    protected $softDeleteableControl;

    protected $group;

    protected $mapping = array(
        'xml' => array(
            'content-type' => 'text/xml',
            'serializer'   => 'xml',
            'filename'     => 'Yardi_%s_%s.xml',
            'group'        => 'xmlReport'
        ),
        'csv' => array(
            'content-type' => 'text/csv',
            'serializer'   => 'csv',
            'filename'     => 'OnePage_%s_%s.csv',
            'group'        => 'csvReport'
        ),
        'promas' => array(
            'content-type' => 'text/csv',
            'serializer'   => 'csv',
            'filename'     => 'Promas_%s_%s.csv',
            'group'        => 'promasReport',
        )
    );

    /**
     * @InjectParams({
     *     "serializer"                 = @Inject("jms_serializer"),
     *     "em"                         = @Inject("doctrine.orm.default_entity_manager"),
     *     "softDeleteableControl"      = @Inject("soft.deleteable.control")
     * })
     */
    public function __construct($serializer, $em, $softDeleteableControl)
    {
        $this->serializer = $serializer;
        $this->em         = $em;
        $this->softDeleteableControl = $softDeleteableControl;
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
        $beginDate = new DateTime($this->begin);
        $endDate = new DateTime($this->end);
        return sprintf(
            $this->mapping[$this->type]['filename'],
            $beginDate->format('Ymd'),
            $endDate->format('Ymd')
        );
    }

    public function setupData($dataFromForm)
    {
        $this->begin = $dataFromForm['begin'].' 00:00:00';
        $this->end = $dataFromForm['end'].' 23:59:59';
        $this->property = $dataFromForm['property'];
        $this->type = $dataFromForm['type'];
        $this->propertyId = $dataFromForm['propertyId'];
        $this->arAccountId = $dataFromForm['arAccountId'];
        $this->accountId = $dataFromForm['accountId'];
        $this->group = $dataFromForm['group'];

        if (!isset($this->mapping[$this->type])) {
            throw new RuntimeException('Report type is invalid');
        }
        $this->isInitialized = true;
    }

    public function getData()
    {
        $this->softDeleteableControl->disable();
        $orderRepository = $this->em->getRepository('DataBundle:Order');
        switch ($this->type) {
            case 'xml':
            case 'csv':
                return $orderRepository->getOrdersForReport($this->property->getId(), $this->begin, $this->end);
            case 'promas':
                return $orderRepository->getOrdersForPromasReport($this->group, $this->begin, $this->end);
        }
    }

    public function getReport($dataFromForm)
    {
        $this->setupData($dataFromForm);

        $group = $this->mapping[$this->type]['group'];
        $serializer  = $this->mapping[$this->type]['serializer'];

        $report = new OrderReport();
        $report->setReceipt($this->getData());
        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups($group);
        $content = $this->serializer->serialize($report, $serializer, $context);

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
