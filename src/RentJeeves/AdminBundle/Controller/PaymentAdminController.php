<?php

namespace RentJeeves\AdminBundle\Controller;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
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

    public function runAction($id)
    {
        $object = $this->admin->getModelManager()->find($this->admin->getClass(), $id);


        $this->request->getSession()->getFlashBag()->add(
            'sonata_flash_success',
            'Payment added to the Job queue'
        );
        return $this->redirect(
            $this->request->headers->get('referer', $this->generateUrl('admin_rentjeeves_data_payment_list'))
        );
    }

    public function batchActionRun(ProxyQueryInterface $selectedModelQuery)
    {
        $action = $this->request->get('action');
        if (false == $this->admin->isGranted(strtoupper($action)) && false == $this->admin->getRoute($action)) {
            throw new AccessDeniedException();
        }
        $redirectResponse = $this->redirect(
            $this->admin->generateUrl('list', array('filter' => $this->admin->getFilterParameters()))
        );

        $modelManager = $this->admin->getModelManager();

        var_dump($this->request->get('idx'));die('OK');
        $target = $modelManager->find($this->admin->getClass(), $this->request->get('targetId'));

        if ($target === null) {
            $this->request->getSession()->getFlashBag()->add('sonata_flash_info', 'flash_batch_merge_no_target');

            return $redirectResponse;
        }
        $selectedModels = $selectedModelQuery->execute();

        // do the merge work here

        try {
//            foreach ($selectedModels as $selectedModel) {
//                $modelManager->delete($selectedModel);
//            }
//
//            $modelManager->update($selectedModel);
        } catch (Exception $e) {
            $this->request->getSession()->getFlashBag()->add('sonata_flash_error', 'flash_batch_merge_error');

            return $redirectResponse;
        }

        $this->request->getSession()->getFlashBag()->add('sonata_flash_success', 'flash_batch_merge_success');

        return $redirectResponse;
    }
}
