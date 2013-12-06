<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Tenant;
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
        $this->testCsv();
        $user = $this->get('security.context')->getToken()->getUser();
        if (!$user->haveAccessToReports()) {
            throw new Exception("Don't have access");
        }

        $group = $this->get('core.session.landlord')->getGroup();
        $formBaseOrder = $this->createForm(new BaseOrderReportType($user, $group));

        $formBaseOrder->handleRequest($this->get('request'));
        if ($formBaseOrder->isValid()) {

            $data = $formBaseOrder->getData();
            $begin = $data['begin'];
            $end = $data['end'];
            $propertyId = $data['property']->getId();
            $type = $data['type'];

            $orderRepository = $this->get('doctrine.orm.default_entity_manager')->getRepository('DataBundle:Order');
            $orders = $orderRepository->getOrdersForReport($propertyId, $begin, $end);

            return $this->get('report.factory')->getBaseReportByType($type, $orders, $begin, $end);
        }

        return array(
            'settings'           => $user->getSettings(),
            'formBaseOrder'      => $formBaseOrder->createView()
        );
    }

    public function testCsv()
    {
        $propertyId = 1;
        $begin = '2010-09-12';
        $end = '2013-12-25';

        $orderRepository = $this->get('doctrine.orm.default_entity_manager')->getRepository('DataBundle:Order');
        $orders = $orderRepository->getOrdersForReport($propertyId, $begin, $end);

        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('csvBaseReportCsv');
        //var_dump($orders);exit;
        $result = $this->get('jms_serializer')->serialize($orders, 'csv', $context);
        var_dump($result);exit;
    }
}
