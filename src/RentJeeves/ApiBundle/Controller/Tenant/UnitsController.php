<?php

namespace RentJeeves\ApiBundle\Controller\Tenant;

use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RentJeeves\ApiBundle\Forms\PropertyType;
use RentJeeves\ApiBundle\Request\Annotation\QueryParam;
use RentJeeves\ApiBundle\Request\Annotation\AttributeParam;
use RentJeeves\ApiBundle\Response\ResponseCollection;
use RentJeeves\ApiBundle\Response\Unit as ResponseEntity;
use RentJeeves\DataBundle\Entity\Property;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UnitsController extends Controller
{
    /**
     * @param Request $request
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Unit",
     *     description="Get all units for a given address.",
     *     statusCodes={
     *         200="Returned when successful",
     *         204="No content with such parameters",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Get("/units")
     * @Rest\View(serializerGroups={"Base", "UnitShort"})
     * @Rest\QueryParam(
     *     name="street",
     *     strict=true,
     *     nullable=false,
     *     description="Street name with number."
     * )
     * @Rest\QueryParam(
     *     name="city",
     *     strict=true,
     *     nullable=false
     * )
     * @Rest\QueryParam(
     *     name="state",
     *     strict=true,
     *     nullable=false
     * )
     * @Rest\QueryParam(
     *     name="zip",
     *     strict=true,
     *     nullable=false
     * )
     *
     * @return ResponseCollection
     */
    public function getUnitsAction(Request $request)
    {
        $form = $this->createForm(new PropertyType(), null, ['method' => 'GET']);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var Property $property */
            $property = $form->getData();

            $result = $this
                ->getDoctrine()
                ->getRepository('RjDataBundle:Unit')
                ->getUnitsByAddress([
                    'street' => $property->getStreet(),
                    'number' => $property->getNumber(),
                    'state' => $property->getArea(),
                    'city' => $property->getCity(),
                    'zip' => $property->getZip(),
                ]);

            if (count($result) > 0) {
                return new ResponseCollection($result);
            }
        } else {
            return $form;
        }
    }

    /**
     * @param int $id
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Unit",
     *     description="Get details for a specific unit.",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Unit not found",
     *         400="Error validating data. Please check parameters and retry.",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Get("/units/{id}")
     * @Rest\View(serializerGroups={"Base", "UnitDetails"})
     * @AttributeParam(
     *     name="id",
     *     encoder = "api.default_id_encoder"
     * )
     *
     * @throws NotFoundHttpException
     * @return ResponseEntity
     */
    public function getUnitAction($id)
    {
        $unit = $this
            ->getDoctrine()
            ->getRepository('RjDataBundle:Unit')
            ->getUnitWithLandlord($id);

        if ($unit) {
            return $this->get('response_resource.factory')->getResponse($unit);
        }

        throw new NotFoundHttpException('Unit not found');
    }
}
