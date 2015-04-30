<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use RentJeeves\DataBundle\Entity\BillingAccount;
use RentJeeves\LandlordBundle\Form\BillingAccountType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditJeeves\ApplicantBundle\Form\Type\PasswordType;
use RentJeeves\LandlordBundle\Form\AccountInfoType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;
use RentJeeves\CheckoutBundle\Controller\Traits\PaymentProcess;
use \RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class SettingsController extends Controller
{
    use FormErrors;
    use PaymentProcess;
    /**
     * @Route("/account/edit", name="landlord_edit_profile")
     * @Template()
     */
    public function editProfileAction()
    {
        $landlord = $this->getUser();

        $form = $this->createForm(
            new AccountInfoType(),
            $landlord
        );
        $request = $this->get('request');

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $translator = $this->get('translator');
                $landlord = $form->getData();
                $em->persist($landlord);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $translator->trans('common.notice.information.updated')
                );
            }
        }
        return array(
            'form'    => $form->createView(),
        );
    }


    /**
     * @Route("/settings/payment_accounts", name="settings_payment_accounts")
     * @Template()
     */
    public function settingsPaymentAccountsAction()
    {
        /** @var Group $group */
        $group = $this->getCurrentGroup();
        $billingAccounts = $group->getBillingAccounts();
        $form = $this->createForm(new BillingAccountType());

        return array(
            'billingAccounts' => $this->get('jms_serializer')->serialize($billingAccounts, 'json'),
            'billingAccountType' => $form->createView(),
            'nGroups' => $this->getGroups()->count(),
        );
    }

    /**
     * @Route(
     *     "/billing/refresh/",
     *     name="landlord_billing_refresh",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="json"},
     *     options={"expose"=true}
     * )
     */
    public function refreshAction()
    {
        /** @var Group $group */
        $group = $this->getCurrentGroup();
        $billingAccounts = $group->getBillingAccounts();
        $data = $this->get('jms_serializer')->serialize($billingAccounts, 'json');

        return new Response($data, 200, array('content-type' => 'application/json'));
    }

    /**
     * @Template()
     */
    public function bankAccountAction()
    {
        $form = $this->createForm(new BillingAccountType());

        return array(
            'bankAccountType' => $form->createView()
        );
    }

    /**
     * @Route(
     *     "/billing/create/",
     *     name="landlord_billing_create",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST"})
     */
    public function createBillingAction(Request $request)
    {
        $logger = $this->get('logger');
        $currentGroup = $this->getCurrentGroup();
        $logger->debug("Creating landlord billing account for " . $currentGroup->getName());
        $billingAccountType = $this->createForm(new BillingAccountType());
        $billingAccountType->handleRequest($request);
        if (!$billingAccountType->isValid()) {
            return $this->renderErrors($billingAccountType, 400);
        }

        try {
            $landlord = $this->getUser();
            $billing = $this->createBillingAccount($billingAccountType, $landlord, $currentGroup);
        } catch (\Exception $e) {
            $logger->error("Exception occurred! " . $e->getMessage());
            return new JsonResponse(
                array(
                    $billingAccountType->getName() => array(
                        '_globals' => explode('|', $e->getMessage())
                    )
                ),
                400
            );
        }

        return new JsonResponse($this->get('jms_serializer')->serialize($billing, 'array'));
    }

    /**
     * @Route(
     *     "/billing/edit/",
     *     name="landlord_billing_edit",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST"})
     */
    public function editBillingAction(Request $request)
    {
        $formType = new BillingAccountType();
        $formData = $request->get($formType->getName());

        $em = $this->getDoctrine()->getManager();
        /** @var BillingAccount $billingAccount */
        $billingAccount = null;
        if (!empty($formData['id'])) {
            $billingAccount = $em->getRepository('RjDataBundle:BillingAccount')->find($formData['id']);
        }

        if (!$billingAccount) {
            return new JsonResponse(
                array('error' => "Payment account #{$formData['id']} not found"),
                400
            );
        }

        $originalIsActive = $billingAccount->getIsActive();
        $billingAccountType = $this->createForm($formType, $billingAccount);
        $billingAccountType->handleRequest($request);
        if (!$billingAccountType->isValid()) {
            return $this->renderErrors($billingAccountType, 400);
        }

        // switching off isActive status is not allowed for active account
        if ($originalIsActive) {
            $billingAccount->setIsActive(1);
        }
        $em->flush();

        return new JsonResponse($this->get('jms_serializer')->serialize($billingAccount, 'array'));
    }

    /**
     * @Route(
     *     "/billing/delete/{accountId}",
     *     name="landlord_billing_delete",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST"})
     */
    public function deleteBillingAction($accountId)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var BillingAccount $billingAccount */
        $billingAccount = $em->getRepository('RjDataBundle:BillingAccount')->find($accountId);

        if (!empty($billingAccount) &&
            $billingAccount->getGroup()->getId() != $this->getCurrentGroup()->getId()
        ) {
            return new JsonResponse(
                array('error' => "Payment account #{$accountId} not found"),
                400
            );
        }

        if ($billingAccount && !$billingAccount->getIsActive()) {
            $em->remove($billingAccount);
            $em->flush();

            return new JsonResponse();
        }

        return new JsonResponse(
            array('error' => 'No available payments to remove'),
            400
        );
    }
}
