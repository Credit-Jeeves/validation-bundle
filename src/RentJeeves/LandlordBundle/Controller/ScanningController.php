<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\CoreBundle\Controller\BaseController;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\LandlordBundle\Form\ScanningCheckType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/scanning")
 */
class ScanningController extends BaseController
{
    /**
     * @Route("/", name="landlord_scanning")
     */
    public function scanningAction(Request $request)
    {
        return $this->render('LandlordBundle:Scanning:index.html.twig');
    }

    /**
     * @Route("/send-form", name="landlord_scanning_check_form")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sendFormAction(Request $request)
    {
        /** @var Holding $holding */
        $holding = $this->getUser()->getHolding();
        if (null == $holding->getProfitStarsSettings() || null == $holding->getProfitStarsSettings()->getMerchantId()) {
            throw new AccessDeniedHttpException();
        }

        $netTellerId = $holding->getProfitStarsSettings()->getMerchantId();
        $secret = $this->container->getParameter('profit_stars.shared_secret');
        $cmid = $this->container->getParameter('profit_stars.cmid');

        $form = $this->createNamedForm(
            '',
            new ScanningCheckType(),
            null,
            [
                'netTellerId' => $netTellerId,
                'secret' => $secret,
                'CMID' => $cmid,
            ]
        );

        return $this->render(
            'LandlordBundle:Scanning:scanningCheck.html.twig',
            ['form' => $form->createView()]
        );
    }
}
