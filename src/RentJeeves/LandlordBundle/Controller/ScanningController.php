<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\ApiBundle\Services\Encoders\Skip32IdEncoder;
use RentJeeves\CoreBundle\Controller\LandlordController;
use RentJeeves\LandlordBundle\Form\LeaseFinderType;
use RentJeeves\LandlordBundle\Form\ScanningCheckType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/scanning")
 */
class ScanningController extends LandlordController
{
    const LEASE_LIMIT = 100;

    /**
     * @Route("/", name="landlord_scanning")
     */
    public function scanningAction(Request $request)
    {
        $form = $this->createForm(new LeaseFinderType());

        return $this->render(
            'LandlordBundle:Scanning:index.html.twig',
            [
                'form' => $form->createView()
            ]
        );
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

    /**
     * @Route("/filter-lease-list", name="landlord_scanning_check_filter_leases", options={"expose"=true})
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function filterLeaseListAction(Request $request)
    {
        $name = $request->get('name') ?: null;
        $email = $request->get('email') ?: null;
        $address = $request->get('address') ?: null;
        $unit = $request->get('unit') ?: null;

        if ($name === null && $email === null && $address === null && $unit === null) {
            throw new \LogicException('Please send your search criteria');
        }

        $data = $this->getContractRepository()->findContractsForScanningTab(
            $this->getCurrentGroup(),
            $name,
            $email,
            $address,
            $unit
        );

        if (count($data) > self::LEASE_LIMIT) {
            $message = $this->getTranslator()->trans(
                'landlord.scanning.lease_list.limit_error',
                ['%%LIMIT%%' => self::LEASE_LIMIT]
            );

            return new JsonResponse(['errorMessage' => $message]);
        }

        $contracts = [];
        foreach ($data as $value) {
            $contracts[] = [
                'name' => sprintf('%s %s', $value['first_name'], $value['last_name']),
                'email' => $value['email'],
                'address' => sprintf('%s %s #%s', $value['number'], $value['street'], $value['unitName']),
                'leaseId' => $this->getSkipEncoder()->encode($value['id'])
            ];
        }

        return new JsonResponse(
            [
                'errorMessage' => null,
                'contracts' => $contracts,
            ]
        );
    }

    /**
     * @Route("/process-scan", name="landlord_scanning_process_scan", options={"expose"=true})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function processScanAction(Request $request)
    {
        $date = new \DateTime('2016-03-01'); // use this temporarily
        $countChecks = $this->get('payment_processor.profit_stars.rdc')
            ->loadScannedChecks($this->getCurrentGroup(), $date);

        return new JsonResponse(['count' => $countChecks]);
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\ContractRepository
     */
    protected function getContractRepository()
    {
        return $this->getEntityManager()->getRepository('RjDataBundle:Contract');
    }

    /**
     * @return Skip32IdEncoder
     */
    protected function getSkipEncoder()
    {
        return $this->get('skip32.id_encoder');
    }
}
