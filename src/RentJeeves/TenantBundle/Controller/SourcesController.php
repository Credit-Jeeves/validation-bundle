<?php
namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\CoreBundle\Controller\BaseController;
use RentJeeves\CheckoutBundle\Controller\Traits\PaymentProcess;
use RentJeeves\CheckoutBundle\PaymentProcessor\SubmerchantProcessorInterface;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;
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
    public function indexAction($mobile = false)
    {
        $paymentAccounts = $this->getDoctrine()
            ->getRepository('RjDataBundle:PaymentAccount')
            ->getActivePaymentAccountsForTenant($this->getUser());

        $pageVars = [
            'paymentAccounts' => $paymentAccounts,
            'needDisplayGroups' => false,
            'isLocked' => $this->getDoctrine()->getManager()->getRepository('RjDataBundle:Tenant')
                ->isPaymentProcessorLocked($this->getUser())
        ];

        if ($mobile) {
            return $this->render('TenantBundle:Sources:index.mobile.html.twig', $pageVars);
        } else {
            return $pageVars;
        }
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
}
