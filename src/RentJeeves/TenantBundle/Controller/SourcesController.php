<?php
namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\CoreBundle\Controller\BaseController;
use RentJeeves\CheckoutBundle\Controller\Traits\PaymentProcess;
use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType;
use RentJeeves\CheckoutBundle\PaymentProcessor\SubmerchantProcessorInterface;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

/**
 * @method \RentJeeves\DataBundle\Entity\Tenant getUser()
 *
 * @Route("/sources")
 */
class SourcesController extends BaseController
{
    use FormErrors;
    use PaymentProcess;

    /**
     * @Route("/", name="tenant_payment_sources")
     * @Template()
     */
    public function indexAction()
    {
        return [
            'paymentAccounts' => $this->getUser()->getPaymentAccounts(),
            'needDisplayGroups' => false,
            'isLocked' => $this->getDoctrine()->getManager()->getRepository('RjDataBundle:Tenant')
                ->isPaymentProcessorLocked($this->getUser())
        ];
    }

    /**
     * @param PaymentAccount $paymentAccount
     *
     * @return RedirectResponse
     *
     * @Route("/del/{id}", name="tenant_payment_sources_del", options={"expose"=true})
     * @ParamConverter("paymentAccount", class="RjDataBundle:PaymentAccount")
     */
    public function delAction(PaymentAccount $paymentAccount)
    {
        $em = $this->getEntityManager();
        if ($this->getUser()->getId() != $paymentAccount->getUser()->getId()) {
            throw $this->createNotFoundException(
                sprintf(
                    'You do not have permission for PaymentAccount with ID#%d',
                    $paymentAccount->getId()
                )
            );
        }

        if ($em->getRepository('RjDataBundle:Tenant')->isPaymentProcessorLocked($this->getUser())) {
            throw new MethodNotAllowedException([], 'Payment Processor is Locked');
        }

        if (false === $em->getRepository('RjDataBundle:PaymentAccount')->isValidForDelete($paymentAccount)) {
            $this->get('session')->getFlashBag()->add('error', 'payment_source.remove.error');

            return $this->redirectToRoute('tenant_payment_sources');
        }

        /** @var SubmerchantProcessorInterface $paymentProcessor */
        $paymentProcessor = $this
            ->get('payment_processor.factory')
            ->getPaymentProcessorByPaymentAccount($paymentAccount);

        if ($paymentProcessor->unregisterPaymentAccount($paymentAccount)) {
            return $this->redirectToRoute('tenant_payment_sources');
        }

        throw new \RuntimeException(
            sprintf(
                'Can\'t remove payment account "%s" with id #%d',
                $paymentAccount->getName(),
                $paymentAccount->getId()
            )
        );
    }

    /**
     * @Route("/save", name="tenant_payment_sources_save", options={"expose"=true})
     * @Method({"POST"})
     */
    public function saveAction(Request $request)
    {
        $em = $this->getEntityManager();
        if ($em->getRepository('RjDataBundle:Tenant')->isPaymentProcessorLocked($user = $this->getUser())) {
            throw new MethodNotAllowedException([], 'Payment Processor is Locked');
        }
        $form = new PaymentAccountType($user);

        $id = null;
        $formData = $request->get($form->getName());

        /** @var PaymentAccount $paymentAccount */
        if (!empty($formData['id'])) {
            $id = $formData['id'];
            $paymentAccount = $em->getRepository('RjDataBundle:PaymentAccount')->findOneWithGroupAddress($id);
        }

        if (empty($paymentAccount)) {
            throw $this->createNotFoundException("Payment Account with ID '{$id}' not found");
        }

        $paymentAccountType = $this->createForm($form, $paymentAccount);
        $paymentAccountType->handleRequest($this->get('request'));
        if (!$paymentAccountType->isValid()) {
            return $this->renderErrors($paymentAccountType);
        }

        try {
            $paymentAccountEntity = $this->updatePaymentAccount($paymentAccountType);
        } catch (\Exception $e) {
            return new JsonResponse([
                $paymentAccountType->getName() => [
                    '_globals' => explode('|', $e->getMessage())
                ]
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'paymentAccount' => $this->get('jms_serializer')->serialize(
                $paymentAccountEntity,
                'array',
                SerializationContext::create()->setGroups(['basic'])
            ),
            'newAddress' => $this->hasNewAddress($paymentAccountType) ?
                $this->get('jms_serializer')->serialize(
                    $paymentAccountEntity->getAddress(),
                    'array'
                ) : null
        ]);
    }
}
