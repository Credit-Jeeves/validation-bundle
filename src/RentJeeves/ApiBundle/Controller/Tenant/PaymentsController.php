<?php

namespace RentJeeves\ApiBundle\Controller\Tenant;

use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RentJeeves\ApiBundle\Request\Annotation\AttributeParam;


class PaymentsController extends Controller
{

    /**
     * @param int $id
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Payments",
     *     description="Show payment details.",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Payment Account not found",
     *         400="Error validating data. Please check parameters and retry.",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Get("/payments/{id}")
     * @Rest\View(serializerGroups={"Base", "PaymentDetails"})
     * @AttributeParam(
     *     name="id",
     *     encoder = "api.default_id_encoder"
     * )
     *
     *
     * @throws NotFoundHttpException
     * @return ResponseEntity
     */
    public function getPaymentsAction($id)
    {
        $payment = $this
            ->getDoctrine()
            ->getRepository('RjDataBundle:Payment')
            ->findOneByIdForUser($id, $this->getUser());

        if ($payment) {
            return $this->get('response_resource.payment')->setEntity($payment);
        }

        throw new NotFoundHttpException('Payment not found');
    }
}