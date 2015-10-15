<?php
namespace RentJeeves\TenantBundle\Controller;

use Doctrine\DBAL\DBALException;
use Guzzle\Http\Exception\CurlException;
use RentJeeves\ComponentBundle\Service\CreditSummaryReport\CreditSummaryReportBuilderInterface;
use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/report")
 *
 */
class ReportController extends Controller
{
    /**
     * @param bool $shouldUpdateReport
     * @return bool
     * @throws \Exception
     */
    protected function isReportLoadAllowed($shouldUpdateReport = false)
    {
        $vendor = $this->container->getParameter('credit_summary_vendor');

        if ($shouldUpdateReport) {
            return $this->getUser()->getLastCompleteReportOperation()->getReportByVendor($vendor);
        } else {
            return !$this->getUser()->getLastReportByVendor($vendor);
        }
    }

    /**
     * @Route("/get/credittrack", name="core_report_get_credittrack")
     * @Template("TenantBundle:Report:get.html.twig")
     *
     * @return array
     */
    public function getCreditTrackAction()
    {
        $this->get('session')->getFlashBag()->set('shouldUpdateReport', true);

        return $this->getAction(null, true);
    }

    /**
     * @Route("/get", name="core_report_get")
     * @Route("/get/{redirect}", name="core_report_get")
     * @Template()
     *
     * @param string|null $redirect
     * @param bool $shouldUpdateReport
     * @return array
     * @throws NotFoundHttpException|\Exception
     */
    public function getAction($redirect = null, $shouldUpdateReport = false)
    {
        if (!$this->isReportLoadAllowed($shouldUpdateReport)) {
            throw $this->createNotFoundException('Report does not allowed');
        }

        return [
            'url' => $this->generateUrl('core_report_get_ajax'),
            'redirect' => $redirect ? $this->generateUrl($redirect) : null
            //$this->getRequest()->headers->get('referer'), //FIXME redirect does not preserve referer
        ];
    }

    /**
     * @param bool|false $shouldUpdateReport
     * @return bool
     * @throws \Exception
     */
    protected function saveCreditSummary($shouldUpdateReport = false)
    {
        if (!$this->isReportLoadAllowed($shouldUpdateReport)) {
            return false;
        }
        /** @var CreditSummaryReportBuilderInterface $reportBuilder */
        $reportBuilder = $this->get('credit_summary.report_builder_factory')->getReportBuilder();
        $reportBuilder->build($this->getUser(), $shouldUpdateReport);

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
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function getAjaxAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $session = $request->getSession();
            ignore_user_abort();
            set_time_limit(90);
            if (false == $session->get('isReportProcessing', false)) {
                $session->set('isReportProcessing', true);
                $shouldUpdateReport = $this->get('session')->getFlashBag()->get('shouldUpdateReport');

                try {
                    if (!$this->saveCreditSummary($shouldUpdateReport)) {
                        return new JsonResponse(['status' => 'finished']);
                    }
                } catch (DBALException $e) {
                    $this->get('fp_badaboom.exception_catcher')->handleException($e);
                    $session->set('isReportProcessing', false);
                    $this->get('session')->getFlashBag()->set(
                        'message_title',
                        $this->get('translator.default')->trans('error.fatal.title')
                    );
                    $this->get('session')->getFlashBag()->set(
                        'message_body',
                        $this->get('translator.default')->trans(
                            'error.fatal.message-%SUPPORT_EMAIL%',
                            ['%SUPPORT_EMAIL%' => $this->container->getParameter('support_email')]
                        )
                    );

                    return new JsonResponse([
                        'status' => 'error',
                        'url' => $this->generateUrl('public_message_flash')
                    ]);
                } catch (CurlException $e) {
                    $this->get('fp_badaboom.exception_catcher')->handleException($e);
                    $this->get('session')->getFlashBag()->set('shouldUpdateReport', $shouldUpdateReport);
                    $session->set('isReportProcessing', false);
                    $this->get('session')->getFlashBag()->set(
                        'message_title',
                        $this->get('translator.default')->trans('error.fatal.title')
                    );

                    return new JsonResponse([
                        'status' => 'warning',
                        'url' => $this->generateUrl('public_message_flash')
                    ]);
                } catch (\Exception $e) {
                    $this->get('fp_badaboom.exception_catcher')->handleException($e);
                    if (4000 == $e->getCode()) {
                        $this->get('session')->getFlashBag()->set('shouldUpdateReport', $shouldUpdateReport);
                        $session->set('isReportProcessing', false);

                        return new JsonResponse(['status' => 'warning']);
                    } else {
                        throw $e;
                    }
                }
                $session->set('isReportProcessing', false);

                return new JsonResponse(['status' => 'finished']);
            }

            return new JsonResponse(['status' => 'processing']);
        }

        return new JsonResponse(['status' => 'processing']);
    }
}
