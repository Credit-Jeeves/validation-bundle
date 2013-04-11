<?php
namespace CreditJeeves\CoreBundle\Controller;

use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Enum\ReportType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\CoreBundle\Experian\NetConnect;
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
     * @var NetConnect
     */
    protected $netConnect;

    /**
     * @todo add all rules
     *
     * @return bool
     */
    protected function isReportLoadAllowed()
    {
        return !$this->getUser()->getReportsPrequal()->last();
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
        require_once __DIR__.'/../sfConfig.php';
        \sfConfig::fill($this->container->getParameter('experian'), 'global_experian');
        \sfConfig::set('global_host', $this->container->getParameter('server_name'));
        $this->netConnect->execute();
        return $this->netConnect->getResponseOnUserData($this->getUser());
    }

    protected function saveArf()
    {
        if (!$this->isReportLoadAllowed()) {
            return false;
        }

        $report = new ReportPrequal();
        $report->setRawData($this->getArf());
        $report->setUser($this->getUser());
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
                    throw $e;
                    $session->set('cjIsArfProcessing', false);
//                        fpErrorNotifier::getInstance()->handler()->handleException($e);
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
     *     "netConnect" = @DI\Inject("core.experian.net_connect")
     * })
     */
    public function setNetConnect(NetConnect $netConnect)
    {
        $this->netConnect = $netConnect;
    }
}
