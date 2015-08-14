<?php

namespace RentJeeves\CheckoutBundle\Controller;

use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializationContext;
use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method Tenant getUser()
 * @Route("/checkout")
 */
class PaymentSourceController extends Controller
{
    /**
     * @Template()
     */
    public function sourceAction()
    {
        $paymentAccountType = $this->createForm(new PaymentAccountType($this->getUser()));

        return ['paymentAccountType' => $paymentAccountType->createView()];
    }

    /**
     * @param $contractId
     * @Route(
     *     "/payment-accounts/list/{contractId}",
     *     name="payment_accounts_list",
     *     options={"expose"=true}
     * )
     * @Method({"GET"})
     *
     * @return Response
     */
    public function getPaymentAccountsListAction($contractId = null)
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.default_entity_manager');

        if ($contractId) {
            /** @var Contract $contract */
            $contract = $em->getRepository('RjDataBundle:Contract')->find($contractId);
        }

        $this->get('soft.deleteable.control')->enable();

        if (!empty($contract)) {
            $paymentAccounts = $this->getDoctrine()
                ->getRepository('RjDataBundle:PaymentAccount')
                ->getPaymentAccountsForTenantByContract($this->getUser(), $contract);
        } else {
            $paymentAccounts = $this->getDoctrine()
                ->getRepository('RjDataBundle:PaymentAccount')
                ->getActivePaymentAccountsForTenant($this->getUser());
        }

        $payAccountsJson = $this->get('jms_serializer')->serialize(
            $paymentAccounts,
            'json',
            SerializationContext::create()->setGroups(['paymentAccounts'])->setSerializeNull(true)
        );

        return new Response($payAccountsJson);
    }

    /**
     * @Route(
     *     "/tenant-addresses/list",
     *     name="tenant_addresses_list",
     *     options={"expose"=true}
     * )
     * @Method({"GET"})
     *
     * @return Response
     */
    public function getAddressesListAction()
    {
        $addressesJson = $this->get('jms_serializer')->serialize(
            $this->getUser()->getAddresses(),
            'json',
            SerializationContext::create()->setGroups(['paymentAccounts'])
        );

        return new Response($addressesJson);
    }
}
