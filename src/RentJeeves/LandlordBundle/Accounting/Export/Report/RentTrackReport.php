<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Report;

use DateTime;
use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\Session\Landlord;
use RentJeeves\DataBundle\Entity\TransactionRepository;
use RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException;
use RentJeeves\LandlordBundle\Accounting\Export\Serializer\ExportSerializerInterface as ExportSerializer;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * accounting.export.renttrack
 */
class RentTrackReport extends ExportReport
{
    protected $em;
    protected $serializer;
    protected $softDeleteableControl;

    /**
     * @param EntityManager    $em
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

    /**
     * {@inheritdoc}
     */
    public function getData(array $settings)
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
        /** @var TransactionRepository $repo */
        $repo = $this->em->getRepository('RjDataBundle:Transaction');
        $exportBy = $settings['export_by'];

        return $repo->getTransactionsForRentTrackReport($groups, $beginDate, $endDate, $exportBy);
    }

    /**
     * @param array $settings
     * @throws ExportException
     */
    protected function validateSettings(array $settings)
    {
        if (!isset($settings['landlord']) || !($settings['landlord'] instanceof Landlord) ||
            !isset($settings['begin']) || !isset($settings['end']) || !isset($settings['export_by']) ||
            !array_key_exists('property', $settings)
        ) {
            throw new ExportException('Not enough parameters for RentTrack report');
        }
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
