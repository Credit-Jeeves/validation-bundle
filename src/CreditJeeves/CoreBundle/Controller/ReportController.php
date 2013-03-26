<?php
namespace CreditJeeves\CoreBundle\Controller;

use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Enum\ReportTypeEnum;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\CoreBundle\Experian\NetConnect;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 * @Route("/report")
 *
 * @method \CreditJeeves\UserBundle\Entity\User getUser()
 */
class ReportController extends Controller
{
    protected $reportType = ReportTypeEnum::PREQUAL;

    /**
     * @var NetConnect
     */
    protected $netConnect;

    /**
     * @Route("/get", name="core_report_get")
     * @Template()
     *
     * @return array
     */
    public function getAction()
    {
        return array(
            'url' => $this->generateUrl('core_report_get_ajax'),
            'redirect' => $this->getRequest()->headers->get('referer')
        );
    }

    protected function getArf()
    {
        require_once __DIR__.'/../sfConfig.php';
        \sfConfig::fill($this->container->getParameter('experian'), 'global_experian');
        \sfConfig::set('global_host', $this->container->getParameter('host'));
        $this->netConnect->execute();
        return $this->netConnect->getResponseOnUserData($this->getUser());
    }

    protected function saveArf()
    {
        $report = new ReportPrequal();
        $report->setRawData($this->getArf());
        $report->setUser($this->getUser());
        $em = $this->getDoctrine()->getManager();
        $em->persist($report);
        $em->flush();
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
