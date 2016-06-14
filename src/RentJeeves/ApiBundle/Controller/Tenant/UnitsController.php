<?php

namespace RentJeeves\ApiBundle\Controller\Tenant;

use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RentJeeves\ApiBundle\Forms\PropertyAddressType;
use RentJeeves\ApiBundle\Request\Annotation\QueryParam;
use RentJeeves\ApiBundle\Request\Annotation\AttributeParam;
use RentJeeves\ApiBundle\Response\ResponseCollection;
use RentJeeves\ApiBundle\Response\Unit as ResponseEntity;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyAddress;
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
     *     },
     *     responseMap={
     *         200 = {
     *             "class"=ResponseEntity::class,
     *             "groups"={"Base", "UnitShort"}
     *         }
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
     * @return ResponseCollection|null
     */
    public function getUnitsAction(Request $request)
    {
        $form = $this->getFormFactory()->createNamed('', new PropertyAddressType(), null, ['method' => 'GET']);
        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var PropertyAddress $propertyAddress */
            $propertyAddress = $form->getData();

            $property = $this->getPropertyManager()->findPropertyByAddressInDb(
                $propertyAddress->getNumber(),
                $propertyAddress->getStreet(),
                $propertyAddress->getCity(),
                $propertyAddress->getState(),
                $propertyAddress->getZip()
            );

            if (null === $property) {
                return null;
            }
            /** @TODO: need remove this functions and use `$property->getUnits()` */
            $result = $this->getUnitRepository()->getUnitsByPropertyWithGroup($property);
            if (count($result) > 0) {
                return new ResponseCollection($result);
            }
        }

        return $form;
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
     *     },
     *     responseMap={
     *         200 = {
     *             "class"=ResponseEntity::class,
     *             "groups"={"Base", "UnitDetails"}
     *         }
     *     }
     * )
     * @Rest\Get("/units/{id}")
     * @Rest\View(serializerGroups={"Base", "UnitDetails"})
     * @AttributeParam(
     *     name="id",
     *     encoder="api.default_id_encoder"
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

    /**
     * @return \RentJeeves\CoreBundle\Services\PropertyManager
     */
    protected function getPropertyManager()
    {
        return $this->get('property.manager');
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\UnitRepository
     */
    protected function getUnitRepository()
    {
        return $this->getDoctrine()->getRepository('RjDataBundle:Unit');
    }

    /**
     * @return \Symfony\Component\Form\FormFactory
     */
    protected function getFormFactory()
    {
        return $this->container->get('form.factory');
    }
}
