<?php

namespace RentJeeves\AdminBundle\Controller;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/")
 */
class AjaxController extends Controller
{
    /**
     * @Route("order/status", name="admin_order_status", options={"expose"=true})
     * @Method({"POST"})
     */
    public function changeOrderStatusAction(Request $request)
    {
        $orderId = $request->request->get('order_id');
        $status = $request->request->get('status');

        $em = $this->getDoctrine()->getManager();
        /** @var Order $order */
        $order = $em->getRepository('DataBundle:Order')->find($orderId);

        if (empty($order)) {
            throw $this->createNotFoundException("Order with ID '{$orderId}' not found");
        }

        $orderStatus = OrderStatus::search($status);

        if (empty($orderStatus)) {
            throw $this->createNotFoundException("Order status '{$status}' not found");
        }

        $order->setStatus($status);

        $em->flush($order);

        return new JsonResponse(
            array(
                'success' => true
            )
        );
    }
}
