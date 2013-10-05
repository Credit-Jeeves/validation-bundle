<?php
namespace RentJeeves\TenantBundle\Controller;

use RentJeeves\CheckoutBundle\Controller\Traits\PaymentProcess;
use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 *
 * @method \RentJeeves\DataBundle\Entity\Tenant getUser()
 *
 * @Route("/sources")
 */
class SourcesController extends Controller
{
    use FormErrors;
    use PaymentProcess;

    /**
     * @Route("/", name="tenant_payment_sources")
     * @Template()
     */
    public function indexAction()
    {
        $paymentAccounts = $this->getUser()->getPaymentAccounts();
        return array(
            'paymentAccounts' => $paymentAccounts
        );
    }

    /**
     * @Route("/del/{id}", name="tenant_payment_sources_del", options={"expose"=true})
     */
    public function delAction(Request $request, $id)
    {
        $paymentAccountType = $this->createForm(new PaymentAccountType($this->getUser()));

        $paymentAccountType->handleRequest($request);
        if (!$paymentAccountType->isValid()) {
            return $this->renderErrors($paymentAccountType);
        }
        $em = $this->getDoctrine()->getManager();
        /** @var PaymentAccount $paymentAccountEntity */
        $paymentAccountEntity = $paymentAccountType->getData();

        $em->persist($paymentAccountEntity);
        $em->flush();

        return new JsonResponse(
            array(
                'success' => true,
                'paymentAccountId' => $paymentAccountEntity->getId()
            )
        );
    }

    /**
     * @Route("/save", name="tenant_payment_sources_save", options={"expose"=true})
     * @Method({"POST"})
     */
    public function saveAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form = new PaymentAccountType($this->getUser());

        $id = null;
        $formData = $request->get($form->getName());

        if (!empty($formData['id'])) {
            $id = $formData['id'];
            $paymentAccount = $em->getRepository('RjDataBundle:PaymentAccount')->find($id);
        }

        if (empty($paymentAccount)) {
            return $this->createNotFoundException("Payment Account with ID '{$id}' not found");
        }


        $paymentAccountType = $this->createForm($form, $paymentAccount);

        return $this->savePaymentAccount($paymentAccountType);
    }
}
