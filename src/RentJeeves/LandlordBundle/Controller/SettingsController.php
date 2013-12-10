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
use RentJeeves\LandlordBundle\Form\DepositType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;
use RentJeeves\CheckoutBundle\Controller\Traits\PaymentProcess;

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
        $paymentAccounts = $group->getBillingAccounts();

        return array(
            'payment_accounts' => $paymentAccounts,
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
     *     "/billing/save/",
     *     name="landlord_billing_save",
     *     options={"expose"=true}
     * )
     * @Method({"POST"})
     */
    public function saveBillingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form = new BankAccountType();

        $id = null;
        $formData = $request->get($form->getName());

        /** @var BillingAccount $billingAccount */
        if (!empty($formData['id'])) {
            $id = $formData['id'];
            $billingAccount = $em->getRepository('RjDataBundle:BillingAccount')->find($id);
        }

        if (empty($billingAccount)) {
            throw $this->createNotFoundException("Payment Account with ID '{$id}' not found");
        }

        $billingAccountType = $this->createForm($form, $billingAccount);
        $billingAccountType->handleRequest($this->get('request'));
        if (!$billingAccountType->isValid()) {
            return $this->renderErrors($billingAccountType);
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
                )
            );
        }

        return new JsonResponse(
            array(
                'success' => true,
                'billingAccount' => $this->get('jms_serializer')->serialize(
                    $billing,
                    'array'
                )
            )
        );
    }
}
