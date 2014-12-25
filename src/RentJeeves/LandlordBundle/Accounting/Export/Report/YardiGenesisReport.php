<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Report;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\LandlordBundle\Accounting\Export\Serializer\ExportSerializerInterface as ExportSerializer;
use RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException;
use DateTime;

/**
 * @Service("accounting.export.yardi_genesis")
 */
class YardiGenesisReport extends ExportReport
{
    protected $em;
    protected $serializer;
    protected $softDeleteableControl;

    /**
     * @InjectParams({
     *     "em" = @Inject("doctrine.orm.default_entity_manager"),
     *     "serializer" = @Inject("export.serializer.yardi_genesis"),
     *     "softDeleteableControl" = @Inject("soft.deleteable.control")
     * })
     */
    public function __construct(EntityManager $em, ExportSerializer $serializer, $softDeleteableControl)
    {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->softDeleteableControl = $softDeleteableControl;
        $this->type = 'yardi_genesis';
        $this->fileType = 'csv';
    }

    public function getContent($settings)
    {
        $this->validateSettings($settings);
        $this->generateFilename($settings);
        $reportData = $this->getData($settings);

        if (empty($reportData)) {
            return null;
        }

        return $this->serializer->serialize($reportData);
    }

    public function getContentType()
    {
        return $this->serializer->getContentType();
    }

    public function getData($settings)
    {
        $this->softDeleteableControl->disable();

        $this->validateSettings($settings);

        $beginDate = $settings['begin'];
        $endDate = $settings['end'];
        $propertyId = $settings['property']->getId();
        $groupId = $settings['landlord']->getGroup()->getId();
        $orderRepository = $this->em->getRepository('DataBundle:Order');

        return $orderRepository->getOrdersForYardiGenesis($beginDate, $endDate, $groupId, $propertyId);
    }

    protected function validateSettings($settings)
    {
        if (!isset($settings['property']) || !($settings['property'] instanceof Property) ||
            !isset($settings['begin']) || !isset($settings['end'])
        ) {
            throw new ExportException('Not enough parameters for Yardi Genesis report');
        }
    }

    protected function generateFilename($params)
    {
        $this->filename = 'PayProc.csv';
    }
}
