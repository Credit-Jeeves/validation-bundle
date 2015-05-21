<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Report;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\CoreBundle\Session\Landlord;
use RentJeeves\LandlordBundle\Accounting\Export\Serializer\ExportSerializerInterface as ExportSerializer;
use RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException;
use DateTime;

/**
 * @Service("accounting.export.promas")
 */
class PromasReport extends ExportReport
{
    protected $em;
    protected $serializer;
    protected $softDeleteableControl;

    /**
     * @InjectParams({
     *     "em" = @Inject("doctrine.orm.default_entity_manager"),
     *     "serializer" = @Inject("export.serializer.promas"),
     *     "softDeleteableControl" = @Inject("soft.deleteable.control")
     * })
     */
    public function __construct(EntityManager $em, ExportSerializer $serializer, $softDeleteableControl)
    {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->softDeleteableControl = $softDeleteableControl;
        $this->type = 'promas';
        $this->fileType = 'csv';
    }

    public function getType()
    {
        return $this->type;
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

        return $orderRepository->getOrdersForPromasReport($groups, $beginDate, $endDate, $exportBy);
    }

    /**
     * @param array $settings
     * @throws ExportException
     */
    protected function validateSettings(array $settings)
    {
        if (!isset($settings['landlord']) || !($settings['landlord'] instanceof Landlord) ||
            !isset($settings['begin']) || !isset($settings['end']) || !isset($settings['export_by'])) {
            throw new ExportException('Not enough parameters for Promas report');
        }
    }

    protected function generateFilename($params)
    {
        $beginDate = new DateTime($params['begin']);
        $endDate = new DateTime($params['end']);

        $this->filename = sprintf(
            'Promas_%s_%s.csv',
            $beginDate->format('Ymd'),
            $endDate->format('Ymd')
        );
    }
}
