<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Report;

use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\Services\SoftDeleteableControl;
use RentJeeves\CoreBundle\Session\Landlord;
use RentJeeves\LandlordBundle\Accounting\Export\Serializer\ExportSerializerInterface as ExportSerializer;
use RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException;
use DateTime;

/**
 * accounting.export.mri_boston_post
 */
class MRIBostonPostReport extends ExportReport
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ExportSerializer
     */
    protected $exportSerializer;

    /**
     * @var SoftDeleteableControl
     */
    protected $softDeleteableControl;

    /**
     * @param EntityManager $em
     * @param ExportSerializer $serializer
     * @param SoftDeleteableControl $softDeleteableControl
     */
    public function __construct(
        EntityManager $em,
        ExportSerializer $serializer,
        SoftDeleteableControl $softDeleteableControl
    ) {
        $this->em = $em;
        $this->exportSerializer = $serializer;
        $this->softDeleteableControl = $softDeleteableControl;
        $this->type = 'boston_post';
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

        return $this->exportSerializer->serialize($reportData);
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->exportSerializer->getContentType();
    }

    /**
     * {@inheritdoc}
     */
    public function getData(array $settings)
    {
        $this->softDeleteableControl->disable();

        $beginDate = $settings['begin'];
        $endDate = $settings['end'];
        $exportBy = $settings['export_by'];
        /** @var Landlord $landlord */
        $landlord = $settings['landlord'];

        if (isset($settings['includeAllGroups']) && $settings['includeAllGroups']) {
            $groups = $landlord->getGroups($landlord->getUser())->toArray();
        } else {
            $groups = [$landlord->getGroup()];
        }
        $orderRepository = $this->em->getRepository('DataBundle:Order');

        return $orderRepository->getOrdersForBostonPostReport($groups, $beginDate, $endDate, $exportBy);
    }

    /**
     * @param array $settings
     * @throws ExportException
     */
    protected function validateSettings(array $settings)
    {
        if (!isset($settings['landlord']) || !($settings['landlord'] instanceof Landlord) ||
            !isset($settings['begin']) || !isset($settings['end']) || !isset($settings['export_by'])) {
            throw new ExportException('Not enough parameters for Boston Post report');
        }
    }

    /**
     * @param array $params
     * @return void
     */
    protected function generateFilename($params)
    {
        $beginDate = new DateTime($params['begin']);
        $endDate = new DateTime($params['end']);

        $this->filename = sprintf(
            'renttrack_%s_%s.csv',
            $beginDate->format('Ymd'),
            $endDate->format('Ymd')
        );
    }
}
