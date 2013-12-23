<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use RentJeeves\DataBundle\Entity\BillingAccount;
use RentJeeves\PublicBundle\Form\BankAccountType;
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
     * @Route("/settings/deposit", name="settings_deposit")
     * @Template()
     */
    public function settingsDepositAction()
    {
        /** @var Group $group */
        $group = $this->getCurrentGroup();
        $billingAccounts = $group->getBillingAccounts();
        $form = $this->createForm(new BankAccountType());

        $data = $this->get('jms_serializer')->serialize($billingAccounts, 'json');

        return array(
            'billingAccounts' => $data,
            'bankAccountType' => $form->createView()
        );
    }

    /**
     * @Template()
     */
    public function bankAccountAction()
    {
        $form = $this->createForm(new BankAccountType());

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
        $formType = new BankAccountType();
        $formData = $this->getRequest()->get($formType->getName());

        $em = $this->getDoctrine()->getManager();
        /** @var BillingAccount $billingAccount */
        $billingAccount = null;
        if (!empty($formData['id'])) {
            $billingAccount = $em->getRepository('RjDataBundle:BillingAccount')->find($formData['id']);
        }

        if (!empty($billingAccount) &&
            $billingAccount->getGroup()->getId() != $this->getUser()->getCurrentGroup()->getId()
        ) {
            throw $this->createNotFoundException("Payment account #'{$formData['id']}' not found");
        }

        $billingAccountType = $this->createForm($formType, $billingAccount);
        $billingAccountType->handleRequest($request);
        if (!$billingAccountType->isValid()) {
            return $this->renderErrors($billingAccountType, 400);
        }

        try {
            $landlord = $this->getUser();
            $this->setMerchantName($this->container->getParameter('rt_merchant_name'));
            $billing = $this->savePaymentAccount($billingAccountType, $landlord, $landlord->getCurrentGroup());
        } catch (\Exception $e) {
            return new JsonResponse(
                array(
                    $billingAccountType->getName() => array(
                        '_globals' => explode('|', $e->getMessage())
                    )
                ), 400
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
        $formType = new BankAccountType();
        $formData = $request->get($formType->getName());

        $em = $this->getDoctrine()->getManager();
        /** @var BillingAccount $billingAccount */
        $billingAccount = null;
        if (!empty($formData['id'])) {
            $billingAccount = $em->getRepository('RjDataBundle:BillingAccount')->find($formData['id']);
        }

        if (!$billingAccount) {
            return new JsonResponse(
                array('error' => "Payment account #'{$formData['id']}' not found"),
                400
            );
        }

        $billingAccountType = $this->createForm($formType, $billingAccount);
        $billingAccountType->handleRequest($request);
        if (!$billingAccountType->isValid()) {
            return $this->renderErrors($billingAccountType, 400);
        }

        $billingAccount->setNickname($formData['nickname']);
        $billingAccount->setIsActive($formData['isActive']);
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
            $billingAccount->getGroup()->getId() != $this->getUser()->getCurrentGroup()->getId()
        ) {
            return new JsonResponse(
                array('error' => "Payment account #'{$accountId}' not found"),
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
