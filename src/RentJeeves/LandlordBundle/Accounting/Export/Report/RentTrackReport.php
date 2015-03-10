<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Report;

use DateTime;
use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\Session\Landlord;
use RentJeeves\DataBundle\Entity\HeartlandRepository;
use RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException;
use RentJeeves\LandlordBundle\Accounting\Export\Serializer\ExportSerializerInterface as ExportSerializer;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * @Service("accounting.export.renttrack")
 */
class RentTrackReport extends ExportReport
{
    protected $em;
    protected $serializer;
    protected $softDeleteableControl;

    /**
     * @InjectParams({
     *     "em" = @Inject("doctrine.orm.default_entity_manager"),
     *     "serializer" = @Inject("export.serializer.renttrack"),
     *     "softDeleteableControl" = @Inject("soft.deleteable.control")
     * })
     * @param EntityManager $em
     * @param ExportSerializer $serializer
     * @param $softDeleteableControl
     */
    public function __construct(EntityManager $em, ExportSerializer $serializer, $softDeleteableControl)
    {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->softDeleteableControl = $softDeleteableControl;
        $this->type = 'renttrack';
        $this->fileType = 'csv';
    }

    public function getData($settings)
    {
        $this->softDeleteableControl->disable();

        $beginDate = $settings['begin'].' 00:00:00';
        $endDate = $settings['end'].' 23:59:59';

        /** @var $landlord Landlord */
        $landlord = $settings['landlord'];

        if (isset($settings['includeAllGroups']) && $settings['includeAllGroups']) {
            $groups = $landlord->getGroups($landlord->getUser());
        } else {
            $groups = [$landlord->getGroup()];
        }
        /** @var HeartlandRepository $repo */
        $repo = $this->em->getRepository('RjDataBundle:Heartland');
        $exportBy = $settings['export_by'];

        return $repo->getTransactionsForRentTrackReport($groups, $beginDate, $endDate, $exportBy);
    }

    protected function validateSettings($settings)
    {
        if (!isset($settings['landlord']) || !($settings['landlord'] instanceof Landlord) ||
            !isset($settings['begin']) || !isset($settings['end']) || !isset($settings['export_by'])) {
            throw new ExportException('Not enough parameters for RentTrack report');
        }
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

    protected function generateFilename($params)
    {
        $beginDate = new DateTime($params['begin']);
        $endDate = new DateTime($params['end']);

        $this->filename = sprintf(
            'RentTrack_%s_%s.csv',
            $beginDate->format('Ymd'),
            $endDate->format('Ymd')
        );
    }
}
