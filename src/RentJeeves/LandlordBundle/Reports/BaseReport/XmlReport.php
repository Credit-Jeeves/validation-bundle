<?php

namespace RentJeeves\LandlordBundle\Reports\BaseReport;

use RentJeeves\LandlordBundle\Reports\ReportInterface;
use CreditJeeves\DataBundle\Entity\Order;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializationContext;
use RentJeeves\LandlordBundle\Model\Reports\BaseOrderReport\YsiTran;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("base.report.xml")
 */
class XmlReport implements ReportInterface
{
    protected $serializer;

    /**
     * @InjectParams({
     *     "em"     = @Inject("jms_serializer"),
     * })
     */
    public function __construct($serializer)
    {
        $this->serializer = $serializer;
    }

    public function getReport($orders, $begin, $end)
    {
        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-Type', 'text/xml');
        $response->headers->set('Content-Disposition', 'attachment; filename=report_'.$begin.'_and_'.$end.'.xml');
        $response->sendHeaders();
        $ysiTran = new YsiTran();
        $ysiTran->setReceipt($orders);
        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('xmlBaseReport');
        //@TODO Find way to implement this options to Context, because now we use SerializedName("TotalAmount")
        //jms_serializer:
        //    property_naming:
        //        separator: ""
        //        lower_case: false

        $response->setContent($this->serializer->serialize($ysiTran, 'xml', $context));
        return $response;
    }
}
