<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\LandlordBundle\Model\Reports\BaseOrderReport\YsiTran;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use \Exception;
use RentJeeves\LandlordBundle\Form\BaseOrderReportType;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializationContext;

/**
 * @Route("/reports")
 */
class ReportsController extends Controller
{
    /**
     * @Route(
     *     "/",
     *     name="landlord_reports"
     * )
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        if (!$user->haveAccessToReports()) {
            throw new Exception("Don't have access");
        }

        $user = $this->get('security.context')->getToken()->getUser();
        $group = $this->get('core.session.landlord')->getGroup();
        $formBaseOrder = $this->createForm(new BaseOrderReportType($user, $group));

        if ($this->get('request')->getMethod() == 'POST') {
            $formBaseOrder->handleRequest($this->get('request'));
            if ($formBaseOrder->isValid()) {

                $orderRepository = $this->get('doctrine.orm.default_entity_manager')->getRepository('DataBundle:Order');
                $data = $formBaseOrder->getData();
                $begin = $data['begin'];
                $end = $data['end'];
                $propertyId = $data['property']->getId();
                $type = $data['type'].'BaseReport';

                $orders = $orderRepository->getOrdersForReport($propertyId, $begin, $end);
                return $this->$type($orders, $begin, $end);
            }
        }
        return array(
            'settings'           => $user->getSettings(),
            'formBaseOrder'      => $formBaseOrder->createView()
        );
    }

    /**
     * XmlBaseReport download
     */
    public function xmlBaseReport($orders, $begin, $end)
    {
        $serializer = $this->get("jms_serializer");
        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-Type', 'text/xml');
        $response->headers->set('Content-Disposition', 'attachment; filename=report_'.$begin.'_and_'.$end.'.xml');
        $response->sendHeaders();
        $ysiTran = new YsiTran();
        $ysiTran->setReceipt($orders);
        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('xmlBaseReport');
        //@TODO Find way to implement this options to Context, because now we use SerializedName("TotalAmount")
        //jms_serializer:
        //    property_naming:
        //        separator: ""
        //        lower_case: false

        $response->setContent($serializer->serialize($ysiTran, 'xml', $context));
        return $response;
    }

    /**
     * CsvBaseReport download
     * @TODO wrtite driver for serialization and move it to order entity
     */
    public function csvBaseReport($orders, $begin, $end)
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
