<?php
namespace CreditJeeves\ExperianBundle\Controller;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\ReportType;
use CreditJeeves\DataBundle\Entity\ReportD2c;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditJeeves\ExperianBundle\NetConnect;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 * @Route("/report")
 *
 * @method \CreditJeeves\DataBundle\Entity\User getUser()
 */
class ReportController extends Controller
{
    protected $reportType = ReportType::PREQUAL;

    protected $redirect = null;

    /**
     * @var \CreditJeeves\ExperianBundle\NetConnect
     */
    protected $netConnect;

    /**
     * @todo add all rules
     *
     * @return bool
     */
    protected function isReportLoadAllowed($isD2c = false)
    {
        if ($isD2c) {
            return $this->getUser()->getLastCompleteReportOperation();
        }
        return !$this->getUser()->getReportsPrequal()->last();
    }

    /**
     * @Route("/get/d2c", name="core_report_get_d2c")
     * @Template("ExperianBundle:Report:get.html.twig")
     *
     * @return array
     */
    public function getD2cAction()
    {
        $this->get('session')->getFlashBag()->set('isD2cReport', true);
//        $this->redirect = ;
        return $this->getAction(null, true);
    }

    /**
     * @Route("/get", name="core_report_get")
     * @Route("/get/{redirect}", name="core_report_get")
     * @Template()
     *
     *
     *
     * @return array
     */
    public function getAction($redirect = null, $isD2c = false)
    {
        if (!$this->isReportLoadAllowed($isD2c)) {
            throw $this->createNotFoundException('Report does not allowed');
        }
        return array(
            'url' => $this->generateUrl('core_report_get_ajax'),
            'redirect' => $redirect?$this->generateUrl($redirect):null
            //$this->getRequest()->headers->get('referer'), //FIXME redirect does not preserve referer
        );
    }

    protected function getArf()
    {
        if (null == $this->netConnect) {
            $this->netConnect = $this->get('experian.net_connect');
        }
        $this->netConnect->execute($this->container);
        return $this->netConnect->getResponseOnUserData($this->get('core.session.applicant')->getUser());
    }

    protected function saveArf($isD2c = false)
    {
        if (!$this->isReportLoadAllowed($isD2c)) {
            return false;
        }

        $em = $this->getDoctrine()->getManager();
        if ($isD2c) {
            $report = $this->getUser()->getLastCompleteOperation(OperationType::REPORT)->getReportD2c();
        } else {
            $report = new ReportPrequal();
            $report->setUser($this->getUser());
        }
        $report->setRawData($this->getArf());
        $em->persist($report);
        $em->flush();
        return true;
    }

    /**
     * @Route(
     *  "/get_ajax",
     *  name="core_report_get_ajax",
     *  defaults={"_format"="json"},
     *  requirements={"_format"="html|json"}
     * )
     * @Method({"GET", "POST"})
     *
     * @return array
     */
    public function getAjaxAction()
    {
        if ($this->getRequest()->isMethod('POST')) {
            $session = $this->getRequest()->getSession();
            ignore_user_abort();
            set_time_limit(90);
            require_once __DIR__.'/../../../../vendor/credit-jeeves/credit-jeeves/lib/curl/CurlException.class.php';
            if (false == $session->get('cjIsArfProcessing', false)) {
                $session->set('cjIsArfProcessing', true);
                $isD2cReport = $this->get('session')->getFlashBag()->get('isD2cReport');
                try {
                    $this->saveArf($isD2cReport);
                } catch (DBALException $e) {
                    $this->get('fp_badaboom.exception_catcher')->handleException($e);
                    $this->get('session')->getFlashBag()->set(
                        'message_title',
                        $this->get('translator.default')->trans('error.fatal.title')
                    );
                    $this->get('session')->getFlashBag()->set(
                        'message_body',
                        $this->get('translator.default')->trans(
                            'error.fatal.message-%SUPPORT_EMAIL%',
                            array('%SUPPORT_EMAIL%' => $this->container->getParameter('support_email'))
                        )
                    );
                    return new JsonResponse(array('url' => $this->generateUrl('public_message_flash')));
                } catch (\CurlException $e) {
                    $this->get('fp_badaboom.exception_catcher')->handleException($e);
                    $this->get('session')->getFlashBag()->set('isD2cReport', $isD2cReport);
                    $session->set('cjIsArfProcessing', false);
                    return new JsonResponse('warning');
                } catch (\ExperianException $e) {
                    $this->get('fp_badaboom.exception_catcher')->handleException($e);
                    if (4000 == $e->getCode()) {
                        $this->get('session')->getFlashBag()->set('isD2cReport', $isD2cReport);
                        $session->set('cjIsArfProcessing', false);
                        return new JsonResponse('warning');
                    } else {
                        throw $e;
                    }
                }
                $session->set('cjIsArfProcessing', false);
                return new JsonResponse('finished');
            }
            return new JsonResponse('processing');
        }


        return new JsonResponse('processing');
    }
}
