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
 * @Service("accounting.export.real_page")
 */
class RealPageReport extends ExportReport
{
    protected $buildingId;

    protected $em;
    protected $serializer;
    protected $softDeleteableControl;

    /**
     * @InjectParams({
     *     "em" = @Inject("doctrine.orm.default_entity_manager"),
     *     "serializer" = @Inject("export.serializer.real_page"),
     *     "softDeleteableControl" = @Inject("soft.deleteable.control")
     * })
     */
    public function __construct(EntityManager $em, ExportSerializer $serializer, $softDeleteableControl)
    {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->softDeleteableControl = $softDeleteableControl;
        $this->type = 'real_page';
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
        $this->setRealPageParams($settings);

        $beginDate = $settings['begin'];
        $endDate = $settings['end'];
        $property = $settings['property'];
        $group = $settings['landlord']->getGroup();
        $exportBy = $settings['export_by'];

        $orderRepository = $this->em->getRepository('DataBundle:Order');

        return $orderRepository->getOrdersForRealPageReport([$group], $property, $beginDate, $endDate, $exportBy);
    }

    public function getBuildingId()
    {
        return $this->buildingId;
    }

    protected function setRealPageParams($params)
    {
        $this->buildingId = $params['buildingId'];
    }

    /**
     * @param array $settings
     * @throws ExportException
     */
    protected function validateSettings(array $settings)
    {
        if (!array_key_exists('property', $settings) ||
            !isset($settings['begin']) || !isset($settings['end']) ||
            !isset($settings['buildingId']) || !isset($settings['export_by'])
        ) {
            throw new ExportException('Not enough parameters for OnePage report');
        }
    }

    protected function generateFilename($params)
    {
        $beginDate = new DateTime($params['begin']);
        $endDate = new DateTime($params['end']);

        $this->filename = sprintf(
            'OnePage_%s_%s.csv',
            $beginDate->format('Ymd'),
            $endDate->format('Ymd')
        );
    }
}
