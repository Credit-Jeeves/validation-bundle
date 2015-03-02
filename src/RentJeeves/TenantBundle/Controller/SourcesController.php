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
use JMS\Serializer\SerializationContext;

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
        $em = $this->getDoctrine()->getManager();
        /** @var PaymentAccount $paymentAccount */
        $paymentAccount = $em->getRepository('RjDataBundle:PaymentAccount')->find($id);
        if (empty($paymentAccount) || $this->getUser()->getId() != $paymentAccount->getUser()->getId()) {
            throw $this->createNotFoundException("Payment Account with ID '{$id}' not found");
        }
        $em->remove($paymentAccount);
        $em->flush($paymentAccount);
        return $this->redirect($request->headers->get('referer'));
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

        // TODO: deal with multiple gruops
        $group = $paymentAccount->getDepositAccounts()->first()->getGroup();

        try {
            $paymentAccountEntity = $this->savePaymentAccount($paymentAccountType, $this->getUser(), $group);
        } catch (\Exception $e) {
            return new JsonResponse(
                array(
                    $paymentAccountType->getName() => array(
                        '_globals' => explode('|', $e->getMessage())
                    )
                )
            );
        }

        return new JsonResponse(
            array(
                'success' => true,
                'paymentAccount' => $this->get('jms_serializer')->serialize(
                    $paymentAccountEntity,
                    'array',
                    SerializationContext::create()->setGroups(array('basic'))
                ),
                'newAddress' => $this->hasNewAddress($paymentAccountType) ?
                    $this->get('jms_serializer')->serialize(
                        $paymentAccountEntity->getAddress(),
                        'array'
                    ) : null
            )
        );
    }
}
