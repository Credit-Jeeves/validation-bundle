<?php

namespace RentJeeves\AdminBundle\Controller;

use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use CreditJeeves\DataBundle\Model\Group;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\DataBundle\Enum\DisputeCode;
use RentJeeves\DataBundle\Entity\BillingAccount;
use RentJeeves\DataBundle\Entity\Heartland;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\YardiClientEnum;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Exception;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/")
 */
class AjaxController extends Controller
{

    protected function makeJsonResponse($data, $groups = array('AdminProperty'))
    {
        $serializer = $this->get('jms_serializer');
        $context = new SerializationContext();
        $context->setGroups($groups);

        if (empty($data)) {
            return new Response(
                $serializer->serialize(
                    array(),
                    $format = 'json',
                    $context
                )
            );
        }

        return new Response(
            $serializer->serialize(
                $data,
                $format = 'json',
                $context
            )
        );
    }

    /**
     * @Route(
     *     "/rj/group/properties",
     *     name="admin_rj_group_properties",
     *     options={"expose"=true}
     * )
     */
    public function propertyAction(Request $request)
    {
        $groupId = $request->request->get('groupId');
        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('DataBundle:Group')->find($groupId);
        if (empty($group)) {
            throw new Exception("Group not found.");
        }
        $properties = $group->getGroupProperties();

        return $this->makeJsonResponse($properties);

    }

    /**
     * @Route(
     *     "/rj/holding/groups",
     *     name="admin_rj_holding_groups",
     *     options={"expose"=true}
     * )
     */
    public function getHoldingGroups(Request $request)
    {
        $holdingId = $request->request->get('holdingId');
        $em = $this->getDoctrine()->getManager();
        /**
         * @var $holding Holding
         */
        $holding = $em->getRepository('DataBundle:Holding')->find($holdingId);
        if (empty($holding)) {
            throw new Exception("Holding not found.");
        }

        return $this->makeJsonResponse($holding->getGroups());
    }

    /**
     * @Route(
     *    "/rj/property/units",
     *     name="admin_rj_group_units",
     *     options={"expose"=true}
     * )
     */
    public function unitAction(Request $request)
    {
        $propertyId = $request->request->get('id');
        $groupId = $request->request->get('groupId');
        $em = $this->getDoctrine()->getManager();
        /**
         * @var $property Property
         */
        $property = $em->getRepository('RjDataBundle:Property')->find($propertyId);

        if ($property && $property->isSingle()) {
            /**
             * @var $singleUnit Unit
             */
            $singleUnit = $property->getSingleUnit();
            $units = array($singleUnit);
        } else {
            $units = $em->getRepository('RjDataBundle:Unit')->findBy(
                array(
                    'group' => $groupId,
                    'property' => $propertyId
                )
            );
        }

        return $this->makeJsonResponse($units, array("AdminUnit"));
    }

    /**
     * @Route(
     *    "/rj/check/yardi/settings",
     *     name="admin_check_yardi_settings",
     *     options={"expose"=true}
     * )
     */
    public function checkYardiSettings()
    {
        $request = $this->getRequest();
        $all = $request->request->all();
        foreach ($all as $fields) {
            foreach ($fields as $key => $data) {
                if ($key === 'yardiSettings') {
                    $settings = $fields[$key];
                }
            }
        }

        if (empty($settings)) {
            throw new NotFoundHttpException();
        }

        $yardiSettings = new YardiSettings();
        foreach ($settings as $key => $value) {
            $method = 'set'.ucfirst($key);
            $yardiSettings->$method($value);
        }
        $clientFactory = $this->get('soap.client.factory');
        /**
         * @var $resident ResidentTransactionsClient
         */
        $resident = $clientFactory->getClient(
            $yardiSettings,
            YardiClientEnum::RESIDENT_TRANSACTIONS
        );

        $result = $resident->getPropertyConfigurations();

        if (empty($result) && $resident->isError()) {
            $response = array(
                'status' => 'error',
                'message'=> $resident->getErrorMessage()
            );
        } else {
            $response = array(
                'status' => 'ok',
                'message'=> $this->get('translator')->trans(
                    'common.test.setting.successfully'
                )
            );
        }

        return new JsonResponse($response);
    }

    /**
     * @Route(
     *    "/rj/residentMapping/tenants",
     *     name="admin_rj_residentMapping_tenants",
     *     options={"expose"=true}
     * )
     */
    public function tenantsAction(Request $request)
    {
        $holdingId = $request->request->get('holdingId');
        $em = $this->getDoctrine()->getManager();
        $tenants = $em->getRepository('RjDataBundle:Tenant')->findByHolding(
            $holdingId
        )->getQuery()->getResult();

        return $this->makeJsonResponse(
            $tenants,
            array("AdminResidentMapping")
        );
    }
    /**
     * @Route(
     *     "/rj/group/{id}/terminal",
     *     name="admin_rj_group_terminal",
     *     options={"expose"=true}
     * )
     */
    public function terminalAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Group $group */
        $group = $em->find('DataBundle:Group', $id);
        /** @var BillingAccount $billingAccount */
        $billingAccount = $group->getActiveBillingAccount();

        if (!$billingAccount) {
            return new JsonResponse(array('message' => 'Payment account not found'));
        }

        $amount = $request->request->get('amount');
        $id4Field = $request->request->get('customData');

        try {
            /** @var Heartland $result */
            $result = $this->get('payment_terminal')->pay($group, $amount, $id4Field);
        } catch (Exception $e) {
            return new JsonResponse(array('message' => 'Payment failed: ' . $e->getMessage()), 200);
        }

        $message = $result->getIsSuccessful() ? 'Payment succeed' : 'Payment failed: ' . $result->getMessages();

        return new JsonResponse(array('message' => $message), 200);
    }

    /**
     * @Route("order/status", name="admin_order_status", options={"expose"=true})
     * @Method({"POST"})
     */
    public function changeOrderStatusAction(Request $request)
    {
        $orderId = $request->request->get('order_id');
        $status = $request->request->get('status');

        $em = $this->getDoctrine()->getManager();
        /** @var Order $order */
        $order = $em->getRepository('DataBundle:Order')->find($orderId);

        if (empty($order)) {
            throw $this->createNotFoundException("Order with ID '{$orderId}' not found");
        }

        $orderStatus = OrderStatus::search($status);

        if (empty($orderStatus)) {
            throw $this->createNotFoundException("Order status '{$status}' not found");
        }

        $order->setStatus($status);

        $em->flush($order);

        return new JsonResponse(
            array(
                'success' => true
            )
        );
    }

    /**
     * @Route("/user/verification_status", name="admin_user_verification_status", options={"expose"=true})
     * @Method({"POST"})
     */
    public function changeUserVerificationStatus(Request $request)
    {
        $userId = $request->request->get('user_id');
        $status = $request->request->get('status');

        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user = $em->getRepository('RjDataBundle:Tenant')->find($userId);

        if (empty($user)) {
            throw $this->createNotFoundException("User with ID '{$userId}' not found");
        }

        $verificationStatus = UserIsVerified::search($status);

        if (empty($verificationStatus)) {
            throw $this->createNotFoundException("Verification status '{$status}' not found");
        }

        $user->setIsVerified($status);

        $em->flush($user);

        return new JsonResponse(
            array(
                'success' => true
            )
        );
    }

    /**
     * @Route("contract/dispute_code", name="admin_contract_dispute_code", options={"expose"=true})
     * @Method({"POST"})
     */
    public function changeContractDisputeCode(Request $request)
    {
        $contractId = $request->request->get('contract_id');
        $disputeCode = $request->request->get('dispute_code');

        $em = $this->getDoctrine()->getManager();

        $contract = $em->getRepository('RjDataBundle:Contract')->find($contractId);

        if (empty($contract)) {
            throw $this->createNotFoundException("Contract not found");
        }

        $disputeCodeConstant = DisputeCode::search($disputeCode);

        if (empty($disputeCodeConstant)) {
            throw $this->createNotFoundException("Specified dispute code does not exist");
        }

        $contract->setDisputeCode($disputeCode);

        $em->flush($contract);

        return new JsonResponse(
            array(
                'success' => true
            )
        );
    }

    /**
     * @Route(
     *    "/rj/property_mapping",
     *     name="admin_property_mapping",
     *     options={"expose"=true}
     * )
     */
    public function getHoldingProperties(Request $request)
    {
        $holdingId = $request->request->get('holdingId');
        $em = $this->getDoctrine()->getManager();
        $holding = $em->getRepository('DataBundle:Holding')->find($holdingId);
        if (!$holding) {
            throw new NotFoundHttpException(sprintf('Holding with id #%s not found', $holdingId));
        }
        $properties = $em->getRepository('RjDataBundle:Property')->findByHolding($holding)->getQuery()->getResult();

        return $this->makeJsonResponse(
            $properties,
            array("AdminProperty")
        );
    }
}
