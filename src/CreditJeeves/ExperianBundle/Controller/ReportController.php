<?php
namespace CreditJeeves\ExperianBundle\Controller;

use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Enum\ReportType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\DiExtraBundle\Annotation as DI;
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

    /**
     * @var \CreditJeeves\ExperianBundle\NetConnect
     */
    protected $netConnect;

    /**
     * @todo add all rules
     *
     * @return bool
     */
    protected function isReportLoadAllowed()
    {
        return !$this->get('core.session.applicant')->getUser()->getReportsPrequal()->last();
    }

    /**
     * @Route("/get", name="core_report_get")
     * @Template()
     *
     * @return array
     */
    public function getAction()
    {
        if (!$this->isReportLoadAllowed()) {
            return new RedirectResponse($this->generateUrl('applicant_homepage'));
        }
        return array(
            'url' => $this->generateUrl('core_report_get_ajax'),
            'redirect' => null//$this->getRequest()->headers->get('referer'),
        );
    }

    protected function getArf()
    {
        $this->netConnect->execute($this->container);
        return $this->netConnect->getResponseOnUserData($this->get('core.session.applicant')->getUser());
    }

    protected function saveArf()
    {
        if (!$this->isReportLoadAllowed()) {
            return false;
        }
        $report = new ReportPrequal();
        $report->setRawData($this->getArf());
        $report->setUser($this->get('core.session.applicant')->getUser());
        $em = $this->getDoctrine()->getManager();
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
            if (!$session->get('cjIsArfProcessing', false)) {
                $session->set('cjIsArfProcessing', true);
                try {
                    $this->saveArf();
                } catch (\Exception $e) {
                    $session->set('cjIsArfProcessing', false);
                    $this->get('fp_badaboom.exception_catcher')->handleException($e);
                    return new JsonResponse('fatal error');
                }
                return new JsonResponse('finished');
            }
            return new JsonResponse('processing');
        }


        return new JsonResponse('processing');
    }

    /**
     * @DI\InjectParams({
     *     "netConnect" = @DI\Inject("experian.net_connect")
     * })
     */
    public function setNetConnect(NetConnect $netConnect)
    {
        $this->netConnect = $netConnect;
    }
}
