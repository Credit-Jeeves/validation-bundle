<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Serializer;

use CreditJeeves\DataBundle\Entity\Operation;
use JMS\Serializer\SerializationContext;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\Serializer\SerializerInterface as Serializer;
use RentJeeves\LandlordBundle\Model\OrderReport;

/**
 * @Service("export.serializer.yardi")
 */
class YardiXmlSerializer implements ExportSerializerInterface
{
    protected $serializer;

    /**
     * @InjectParams({
     *     "serializer" = @Inject("jms_serializer")
     * })
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function serialize($data)
    {
        $report = new OrderReport();
        $report->setReceipt($data);

        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('xmlReport');

        $strategy = new SkipPropertyExclusionStrategy(
            ['Details'],
            array(null),
            true
        );

        $context->addExclusionStrategy($strategy);

        $content = $this->serializer->serialize($report, 'yardi', $context);

        return $content;
    }

    public function getContentType()
    {
        return 'text/xml';
    }
}
