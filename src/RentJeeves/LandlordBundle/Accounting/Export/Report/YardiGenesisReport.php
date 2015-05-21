<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Report;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\LandlordBundle\Accounting\Export\Serializer\ExportSerializerInterface as ExportSerializer;
use RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException;

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

    /**
     * {@inheritdoc}
     */
    public function getContent(array $settings)
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

    /**
     * {@inheritdoc}
     */
    public function getData(array $settings)
    {
        $this->softDeleteableControl->disable();

        $this->validateSettings($settings);

        $beginDate = $settings['begin'];
        $endDate = $settings['end'];
        $property = $settings['property'];
        $orderRepository = $this->em->getRepository('DataBundle:Order');
        $exportBy = $settings['export_by'];

        /** @var $landlord Landlord */
        $landlord = $settings['landlord'];

        if (isset($settings['includeAllGroups']) && $settings['includeAllGroups']) {
            $groups = $landlord->getGroups($landlord->getUser())->toArray();
        } else {
            $groups = [$landlord->getGroup()];
        }

        return $orderRepository->getOrdersForYardiGenesis($beginDate, $endDate, $groups, $exportBy, $property);
    }

    /**
     * @param array $settings
     * @throws ExportException
     */
    protected function validateSettings(array $settings)
    {
        if (!isset($settings['begin']) || !isset($settings['end']) ||
            !isset($settings['export_by']) || !array_key_exists('property', $settings)
        ) {
            throw new ExportException('Not enough parameters for Yardi Genesis report');
        }
    }

    protected function generateFilename($params)
    {
        $this->filename = 'PayProc.csv';
    }
}
