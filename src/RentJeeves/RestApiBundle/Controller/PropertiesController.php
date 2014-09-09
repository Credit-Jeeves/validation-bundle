<?php

namespace RentJeeves\RestApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

class PropertiesController extends Controller
{
    /**
     * @param $id
     *
     * @ApiDoc()
     * @Rest\View()
     *
     * @return array
     */
    public function getPropertyAction($id)
    {
        return ['id' => $id];
    }

    /**
     * @param ParamFetcher $paramFetcher
     *
     * @ApiDoc()
     * @Rest\View()
     * @Rest\QueryParam(
     *   name="address",
     *   requirements="\w+",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @return array
     */
    public function searchPropertyAction(ParamFetcher $paramFetcher)
    {
        $address = $paramFetcher->get('address');

        return ['address' => $address];
    }

    /**
     * @param $propertyId
     * @param $unitId
     *
     * @ApiDoc()
     * @Rest\View()
     * @Rest\Get("/properties/{propertyId}/units/{unitId}")
     *
     * @return array
     */
    public function getPropertyUnitAction($propertyId, $unitId)
    {
        return ['propertyId' => $propertyId, 'unitId' => $unitId];
    }

    /**
     * @param $propertyId
     *
     * @ApiDoc()
     * @Rest\View()
     * @Rest\Get("/properties/{propertyId}/units")
     *
     * @return array
     */
    public function cgetPropertyUnitsAction($propertyId)
    {
        return ['propertyId' => $propertyId, 'units' => []];
    }

    /**
     * @param ParamFetcher $paramFetcher
     *
     * @ApiDoc()
     * @Rest\View()
     * @Rest\Post()
     * @Rest\RequestParam(
     *   name="address",
     *   key="address",
     *   strict=true,
     *   nullable=false
     * )
     * @Rest\RequestParam(
     *   name="isSingle",
     *   key="is_single",
     *   strict=true,
     *   nullable=false
     * )
     * @Rest\RequestParam(
     *   name="unitName",
     *   key="unit_name",
     *   strict=true,
     *   nullable=true
     * )
     *
     * @return array
     */
    public function createPropertiesAction(ParamFetcher $paramFetcher)
    {
        return ['propertyId' => 1];
    }
}
