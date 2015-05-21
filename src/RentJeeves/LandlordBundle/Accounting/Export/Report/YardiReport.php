<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Report;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\CoreBundle\Session\Landlord;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\LandlordBundle\Accounting\Export\Serializer\ExportSerializerInterface as ExportSerializer;
use RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException;
use DateTime;

/**
 * @Service("accounting.export.yardi")
 */
class YardiReport extends ExportReport
{
    protected $propertyId;
    protected $arAccountId;
    protected $accountId;

    protected $em;
    protected $serializer;
    protected $softDeleteableControl;

    /**
     * @InjectParams({
     *     "em" = @Inject("doctrine.orm.default_entity_manager"),
     *     "serializer" = @Inject("export.serializer.yardi"),
     *     "softDeleteableControl" = @Inject("soft.deleteable.control")
     * })
     */
    public function __construct(EntityManager $em, ExportSerializer $serializer, $softDeleteableControl)
    {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->softDeleteableControl = $softDeleteableControl;
        $this->type = 'yardi';
        $this->fileType = 'xml';
    }

    public function getContent(array $settings)
    {
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
        $this->setYardiParams($settings);

        $beginDate = $settings['begin'];
        $endDate = $settings['end'];
        $property = $settings['property'];
        $holding = $settings['landlord']->getUser()->getHolding();

        $repository = $this->em->getRepository('DataBundle:Operation');

        return $repository->getOperationsForXmlReport(
            $property,
            $holding,
            $beginDate,
            $endDate
        );
    }

    public function getPropertyId()
    {
        return $this->propertyId;
    }

    protected function setYardiParams($params)
    {
        $this->propertyId = $params['propertyId'];
    }

    /**
     * @param array $settings
     * @throws ExportException
     */
    protected function validateSettings(array $settings)
    {
        if (!array_key_exists('property', $settings) ||
            !isset($settings['landlord']) || !($settings['landlord'] instanceof Landlord) ||
            !isset($settings['propertyId']) ||
            !isset($settings['begin']) || !isset($settings['end'])) {
            throw new ExportException('Not enough parameters for Yardi report');
        }
    }

    protected function generateFilename($params)
    {
        $beginDate = new DateTime($params['begin']);
        $endDate = new DateTime($params['end']);

        $this->filename = sprintf(
            'Yardi_%s_%s.xml',
            $beginDate->format('Ymd'),
            $endDate->format('Ymd')
        );
    }
}
