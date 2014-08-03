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

    public function getContent($settings)
    {
        $this->generateFilename($settings);
        $reportData = $this->getData($settings);

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
        $this->setYardiParams($settings);

        $beginDate = $settings['begin'].' 00:00:00';
        $endDate = $settings['end'].' 23:59:59';
        $propertyId = $settings['property']->getId();
        $repository = $this->em->getRepository('DataBundle:Operation');

        return $repository->getOperationsForXmlReport($propertyId, $beginDate, $endDate);
    }

    public function getAccountId()
    {
        return $this->accountId;
    }

    public function getArAccountId()
    {
        return $this->arAccountId;
    }

    public function getPropertyId()
    {
        return $this->propertyId;
    }

    protected function setYardiParams($params)
    {
        $this->propertyId = $params['propertyId'];
        $this->arAccountId = $params['arAccountId'];
        $this->accountId = $params['accountId'];
    }

    protected function validateSettings($settings)
    {
        if (!isset($settings['property']) || !($settings['property'] instanceof Property) ||
            !isset($settings['propertyId']) || !isset($settings['arAccountId']) || !isset($settings['accountId']) ||
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
