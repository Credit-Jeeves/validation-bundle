<?php

namespace RentJeeves\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RentJeeves\ApiBundle\Services\InviteLandlord;
use RentJeeves\PublicBundle\Form\InviteType;
use RentJeeves\PublicBundle\Form\LandlordType;
use Symfony\Component\HttpFoundation\Request;

class LandlordsController extends Controller
{
    /**
     * @param Request $request
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Landlords",
     *     description="Create invite for landlord.",
     *     statusCodes={
     *         200="Success",
     *         400="Error validating data",
     *         500="Something went wrong with the request"
     *     }
     * )
     * @Rest\Post("/landlords")
     * @Rest\RequestParam(
     *   name="unit_id",
     *   strict=true,
     *   nullable=true
     * )
     * @Rest\RequestParam(
     *   name="landlord",
     *   strict=true,
     *   nullable=false
     * )
     * @Rest\View(serializerGroups={"LandlordDetails"})
     *
     * @return array
     */
    public function newLandlordAction(Request $request)
    {
        /** @var InviteLandlord $inviteProcesser */
//        $inviteProcesser = $this->get('api.invite.landlord');
//
//        $form = $this->createForm(
//            new InviteType()
//        );
//
//        $request = $this->get('request');
//        $form->handleRequest($request);
//        if ($form->isValid()) {
//            $invite = $form->getData();
////            $invite->setUnit();
//            $invite->setTenant($this->getUser());
//
//            $landlord = $inviteProcesser->invite($invite, $this->getUser());
//
//            if ($landlord) {
//                return $this->view([
//                    'status' => 'OK',
//                    'landlord_id' => $landlord->getId()
//                ], Codes::HTTP_CREATED);
//            }
//        }
//        return $this->view($form);
    }
}
