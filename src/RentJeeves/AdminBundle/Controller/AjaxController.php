<?php

namespace RentJeeves\AdminBundle\Controller;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use RentJeeves\DataBundle\Enum\DisputeCode;
use RentJeeves\DataBundle\Entity\BillingAccount;
use RentJeeves\DataBundle\Entity\Heartland;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Exception;

/**
 * @Route("/")
 */
class AjaxController extends Controller
{
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
}
