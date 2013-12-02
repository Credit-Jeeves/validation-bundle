<?php

namespace RentJeeves\LandlordBundle\Reports\BaseReport;

use RentJeeves\LandlordBundle\Reports\ReportInterface;
use CreditJeeves\DataBundle\Entity\Order;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\HttpFoundation\Response;
use CreditJeeves\DataBundle\Enum\OrderType;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("base.report.csv")
 */
class CsvReport implements ReportInterface
{
    /**
     * @TODO wrtite driver for serialization and move it to order entity
     */
    public function getReport($orders, $begin, $end)
    {
        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename=report_'.$begin.'_and_'.$end.'.csv');
        $response->sendHeaders();
        $keys = false;
        $fp = fopen('php://temp', 'r+');
        /**
         * @var $order Order
         */
        foreach ($orders as $order) {
            $data = array();
            /**
             * @var $property Property
             */
            $property = $order->getContract()->getProperty();
            $data['Property'] = $property->getFullAddress();
            $unit = $order->getContract()->getUnit();
            $unitName = '';
            if ($unit) {
                $unitName = ' #'.$unit->getName();
            }
            $data['Unit'] = $unitName;
            $data['Date'] = $order->getUpdatedAt()->format('c');
            $data['Amount'] = $order->getAmount();
            /**
             * @var $tenant Tenant
             */
            $tenant = $order->getContract()->getTenant();
            $data['Firts_Name'] = $tenant->getFirstName();
            $data['Last_Name'] = $tenant->getLastName();
            if ($order->getType() === OrderType::HEARTLAND_CARD) {
                $data['Code'] = 'PMTCRED';
            } elseif ($order->getType() === OrderType::HEARTLAND_BANK) {
                $data['Code'] = 'PMTCHECK';
            } else {
                $data['Code'] = '';
            }
            $data['Description'] = $data['Property'].$data['Unit'];
            $data['Description'] .= ' '.$order->getType().' '.$order->getHeartlandTransactionId();

            if (!$keys) {
                $keys = true;
                fputcsv($fp, array_keys($data), $delimiter = ",", $enclosure = '"');
            }
            fputcsv($fp, $data, $delimiter = ",", $enclosure = '"');
        }

        rewind($fp);
        $data = fread($fp, 1048576);
        fclose($fp);
        $response->setContent($data);
        return $response;
    }
}
