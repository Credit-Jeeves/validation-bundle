<?php

namespace RentJeeves\CheckoutBundle\Controller;

use CreditJeeves\DataBundle\Entity\Group;
use JMS\Serializer\SerializationContext;
use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType;
use RentJeeves\CheckoutBundle\PaymentProcessor\SubmerchantProcessorInterface;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method Tenant getUser()
 * @Route("/checkout")
 */
class PaymentSourceController extends Controller
{
    use FormErrors;
    use Traits\PaymentProcess;

    /**
     * @Template()
     * @param string|null $formNameSuffix
     * @return array
     */
    public function sourceAction($formNameSuffix = null)
    {
        $paymentAccountType = $this->createForm(
            new PaymentAccountType(
                $this->getUser(),
                $formNameSuffix,
                $this->getDoctrine()->getManager()
            )
        );

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
        if ($contractId) {
            /** @var Contract $contract */
            $contract = $this->getDoctrine()->getRepository('RjDataBundle:Contract')->find($contractId);
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

    /**
     * @Route(
     *      "/source/{formNameSuffix}",
     *      name="checkout_pay_source",
     *      options={"expose"=true},
     *      defaults={"formNameSuffix"=null}
     * )
     * @Method({"POST"})
     * @param Request $request
     * @param string|null $formNameSuffix
     * @return JsonResponse
     */
    public function createSourceAction(Request $request, $formNameSuffix = null)
    {
        $paymentAccountType = $this->createForm(
            new PaymentAccountType(
                $this->getUser(),
                $formNameSuffix,
                $this->getDoctrine()->getManager()
            )
        );
        $paymentAccountType->handleRequest($request);
        if (!$paymentAccountType->isValid()) {
            return $this->renderErrors($paymentAccountType);
        }

        try {
            $contractId = $paymentAccountType->get('contractId')->getData();
            $groupId = $request->get('group_id');
            $depositAccountType = $request->get('deposit_account_type', DepositAccountType::RENT);

            if ($contractId) {
                /** @var Contract $contract */
                $contract = $this->getDoctrine()
                    ->getRepository('RjDataBundle:Contract')
                    ->find($contractId);
                $group = $contract->getGroup();
                $tenant = $contract->getTenant();
            } elseif ($groupId) {
                $group = $this->getDoctrine()
                    ->getRepository('DataBundle:Group')
                    ->find($groupId);
                $tenant = $this->getUser();
            }
            if (empty($contract) && empty($group)) {
                throw new \Exception('Contract and Group are undefined.');
            }
            $paymentAccountEntity = $this->savePaymentAccount(
                $paymentAccountType,
                $group,
                $tenant,
                $depositAccountType
            );
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

    /**
     * @Route(
     *      "/source_existing/{formNameSuffix}",
     *      name="checkout_pay_existing_source",
     *      options={"expose"=true},
     *      defaults={"formNameSuffix"=null}
     * )
     * @Method({"POST"})
     * @param Request $request
     * @param string|null $formNameSuffix
     * @return JsonResponse
     */
    public function sourceExistingAction(Request $request, $formNameSuffix = null)
    {
        $formType = new PaymentAccountType(
            $this->getUser(),
            $formNameSuffix,
            $this->getDoctrine()->getManager()
        );
        $formData = $request->get($formType->getName());

        $paymentAccountId = $formData['id'];
        $contractId = $request->get('contract_id');
        $groupId = $request->get('group_id');
        $depositAccountType = $request->get('deposit_account_type', DepositAccountType::RENT);

        /** @var PaymentAccount $paymentAccount */
        $paymentAccount = $this->getDoctrine()->getRepository('RjDataBundle:PaymentAccount')->find($paymentAccountId);
        if ($contractId &&
            $contract = $this->getDoctrine()->getRepository('RjDataBundle:Contract')->find($contractId)
        ) {
            $group = $contract->getGroup();
        } elseif ($groupId) {
            $group = $this->getDoctrine()->getRepository('DataBundle:Group')->find($groupId);
        }

        try {
            /** @var Group $group */
            if (empty($group) || empty($paymentAccount)) {
                throw new \Exception('Group or Payment Account is undefined');
            }

            $depositAccount = $group->getDepositAccount(
                $depositAccountType,
                $group->getGroupSettings()->getPaymentProcessor()
            );

            /** @var SubmerchantProcessorInterface $paymentProcessor */
            $paymentProcessor = $this->get('payment_processor.factory')->getPaymentProcessor($group);
            $paymentAccountMapped = $this->get('payment_account.type.mapper')
                ->map($this->createForm($formType, $paymentAccount));
            $paymentProcessor->registerPaymentAccount($paymentAccountMapped, $depositAccount);
        } catch (\Exception $e) {
            return new JsonResponse([
                $formType->getName() => [
                    '_globals' => explode('|', $e->getMessage())
                ]
            ]);
        }

        return new JsonResponse(['success' => true]);
    }
}
