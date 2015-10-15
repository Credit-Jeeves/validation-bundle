<?php
namespace RentJeeves\TenantBundle\Controller;

use Doctrine\DBAL\DBALException;
use Guzzle\Http\Exception\CurlException;
use Monolog\Logger;
use RentJeeves\ComponentBundle\CreditSummaryReport\CreditSummaryReportBuilderInterface;
use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/report")
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

        $this->getFlashBag()->set('shouldUpdateReport', $shouldUpdateReport);

        return [
            'url' => $this->generateUrl('core_report_get_ajax'),
            'redirect' => $redirect ? $this->generateUrl($redirect) : null
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
            /** @var Logger $logger */
            $logger = $this->get('logger');
            ignore_user_abort();
            set_time_limit(90);
            if (false == $session->get('isReportProcessing', false)) {
                $session->set('isReportProcessing', true);
                $shouldUpdateReport = $this->getFlashBag()->get('shouldUpdateReport');

                try {
                    if (!$this->saveCreditSummary($shouldUpdateReport)) {
                        $session->set('isReportProcessing', false);
                        $logger->debug(
                            '[Report Controller]Load credit summary report is not allowed for user #' .
                            $this->getUser()->getId()
                        );

                        $title = $this->getTranslator()->trans('load.report.is_not_allowed.title');
                        $body = $this->getTranslator()->trans(
                            'load.report.is_not_allowed.message-%SUPPORT_EMAIL%',
                            ['%SUPPORT_EMAIL%' => $this->container->getParameter('support_email')]
                        );

                        return $this->createShowMessageResponse($title, $body);
                    }
                } catch (DBALException $e) {
                    $session->set('isReportProcessing', false);
                    $logger->alert('[Report Controller]' . $e->getMessage());

                    return $this->createShowMessageResponse();
                } catch (CurlException $e) {
                    $session->set('isReportProcessing', false);
                    $logger->alert('[Report Controller]' . $e->getMessage());

                    return $this->createShowMessageResponse();
                } catch (\Exception $e) {
                    $session->set('isReportProcessing', false);

                    if (4000 == $e->getCode()) { // need retry
                        $logger->debug('[Report Controller]' . $e->getMessage());
                        $this->getFlashBag()->set('shouldUpdateReport', $shouldUpdateReport);

                        return new JsonResponse('warning');
                    } else {
                        $logger->alert('[Report Controller]' . $e->getMessage());

                        return $this->createShowMessageResponse();
                    }
                }
                $session->set('isReportProcessing', false);

                return new JsonResponse('finished');
            }

            return new JsonResponse('processing');
        }

        return new JsonResponse('processing');
    }

    /**
     * @param string $title
     * @param string $body
     * @return JsonResponse
     */
    protected function createShowMessageResponse($title = '', $body = '')
    {
        if (!$title) {
            $title = $this->getTranslator()->trans('error.fatal.title');
        }
        if (!$body) {
            $body = $this->getTranslator()->trans(
                'error.fatal.message-%SUPPORT_EMAIL%',
                ['%SUPPORT_EMAIL%' => $this->container->getParameter('support_email')]
            );
        }

        $this->getFlashBag()->set('message_title',$title);
        $this->getFlashBag()->set('message_body', $body);

        return new JsonResponse(['url' => $this->generateUrl('public_message_flash')]);
    }

    /**
     * @return FlashBagInterface
     */
    protected function getFlashBag()
    {
        return $this->get('session')->getFlashBag();
    }
}
