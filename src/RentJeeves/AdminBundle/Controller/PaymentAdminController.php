<?php

namespace RentJeeves\AdminBundle\Controller;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use DateTime;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentRepository;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use JMS\DiExtraBundle\Annotation as DI;
use \Exception;
use Symfony\Component\HttpFoundation\Session\Session;

class PaymentAdminController extends CRUDController
{
    /**
     * Trick for JMS DI
     * @DI\Inject("request", strict = false)
     * @var Request
     */
    private $request;

//    /**
//     * @param $id
//     * @return RedirectResponse
//     *
//     * FIXME add warning message with approval
//     *
//     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
//     */
//    public function runAction($id)
//    {
//        $object = $this->admin->getModelManager()->find($this->admin->getClass(), $id);
//
//        if (empty($object)) {
//            throw $this->createNotFoundException("Payment with id '{$id}' not found");
//        }
//
//        $em = $this->getDoctrine()->getManager();
//        $em->persist($object->createJob());
//        $em->flush();
//
//        $this->request->getSession()->getFlashBag()->add(
//            'sonata_flash_success',
//            'Payment added to the Job queue'
//        );
//        return $this->redirect(
//            $this->request->headers->get('referer', $this->generateUrl('admin_rentjeeves_data_payment_list'))
//        );
//    }

    public function batchActionRun(ProxyQueryInterface $selectedModelQuery)
    {
        $action = $this->request->get('action');
        if (false == $this->admin->isGranted(strtoupper($action)) && false == $this->admin->getRoute($action)) {
            throw new AccessDeniedException();
        }
        $redirectResponse = $this->redirect(
            $this->admin->generateUrl('list', array('filter' => $this->admin->getFilterParameters()))
        );

        $em = $this->getDoctrine()->getManager();
        /** @var PaymentRepository $repository */
        $repository = $this->getDoctrine()->getRepository($this->admin->getClass());

        $date = new DateTime();

//        $filter = $this->request->get('filter');
//        if (!empty($filter['startDate']['value']['day'])) {
//            $day = $filter['startDate']['value']['day']?:$day;
//            $month = $filter['startDate']['value']['month']?:$month;
//            $year = $filter['startDate']['value']['year']?:$year;
//        }
        $i = 0;
        /** @var Payment $payment */
        foreach ($repository->getActivePayments($date, $this->request->get('idx')) as $payment) {
            $em->persist($payment->createJob());
            $i++;
        }
        if (0 == $i) {
            $this->request->getSession()->getFlashBag()->add('sonata_flash_warning', 'admin.butch.run.warning');
        } else {
            $em->flush();
            $this->request->getSession()->getFlashBag()->add(
                'sonata_flash_success',
                $this->get('translator')->trans('admin.butch.run.success-%NUMBER%', array('%NUMBER%' => $i))
            );
        }

        return $redirectResponse;
    }
}
