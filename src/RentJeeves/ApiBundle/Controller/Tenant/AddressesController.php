<?php

namespace RentJeeves\ApiBundle\Controller\Tenant;

use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RentJeeves\ApiBundle\Response\Address as ResponseEntity;
use RentJeeves\ApiBundle\Response\ResponseCollection;
use RentJeeves\ApiBundle\Request\Annotation\AttributeParam;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AddressesController extends Controller
{
    /**
     * @ApiDoc(
     *     resource=true,
     *     section="Tenant Address",
     *     description="This call allows to get all addresses that belong to the tenant.",
     *     statusCodes={
     *         200="Returned when successful",
     *         204="No content with such parameters",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Get("/addresses")
     * @Rest\View(serializerGroups={"Base", "AddressDetails"})
     *
     * @return ResponseCollection
     */
    public function getAddressesAction()
    {
        /** @var Tenant $user */
        $user = $this->getUser();

        $response = new ResponseCollection($user->getAddresses()->toArray());

        if ($response->count() > 0) {
            return $response;
        }
    }

    /**
     * @param int $id
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Tenant Address",
     *     description="This call allows the application to get detailed information about address by id.",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Address not found",
     *         400="Error validating data. Please check parameters and retry.",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Get("/addresses/{id}")
     * @Rest\View(serializerGroups={"Base", "AddressDetails"})
     * @AttributeParam(
     *     name="id",
     *     encoder="api.default_id_encoder"
     * )
     *
     * @throws NotFoundHttpException
     * @return ResponseEntity
     */
    public function getAddressAction($id)
    {
        $address = $this
            ->getDoctrine()
            ->getRepository('DataBundle:Address')
            ->findOneBy(['user' => $this->getUser(), 'id' => $id]);

        if ($address) {
            return $this->get('response_resource.factory')->getResponse($address);
        }

        throw new NotFoundHttpException('Address not found');
    }
}
