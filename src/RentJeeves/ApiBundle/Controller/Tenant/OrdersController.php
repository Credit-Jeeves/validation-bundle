<?php

namespace RentJeeves\ApiBundle\Controller\Tenant;

use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use RentJeeves\ApiBundle\Request\Annotation\AttributeParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RentJeeves\ApiBundle\Response\Order as ResponseEntity;
use RentJeeves\ApiBundle\Response\ResponseCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrdersController extends Controller
{
    /**
     * @ApiDoc(
     *     resource=true,
     *     section="Order",
     *     description="Get all orders for tenant.",
     *     statusCodes={
     *         200="Returned when successful",
     *         204="No content with such parameters",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Get("/orders")
     * @Rest\View(serializerGroups={"Base", "OrderShort"})
     *
     * @return ResponseCollection
     */
    public function getOrdersAction()
    {
        $orders = $this
            ->getDoctrine()
            ->getRepository('DataBundle:Order')
            ->getUserOrders($this->getUser());

        $response = new ResponseCollection($orders);

        if ($response->count() > 0) {
            return $response;
        }
    }

    /**
     * @param int $id
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Order",
     *     description="Get details for a specific order.",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Order not found",
     *         400="Error validating data. Please check parameters and retry.",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Get("/orders/{id}")
     * @Rest\View(serializerGroups={"Base", "OrderDetails"})
     * @AttributeParam(
     *     name="id",
     *     encoder="api.default_id_encoder"
     * )
     *
     * @throws NotFoundHttpException
     * @return ResponseEntity
     */
    public function getOrderAction($id)
    {
        // disable for get payment_resource
        $this->get('soft.deleteable.control')->disable();

        $order = $this
            ->getDoctrine()
            ->getRepository('DataBundle:Order')
            ->findOneBy(['id' => $id, 'user' => $this->getUser()]);

        if ($order) {
            return $this->get('response_resource.factory')->getResponse($order);
        }

        throw new NotFoundHttpException('Order not found');
    }
}
