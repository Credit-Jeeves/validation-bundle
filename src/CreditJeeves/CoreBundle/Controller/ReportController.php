<?php
namespace CreditJeeves\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\CoreBundle\Experian\NetConnect;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 * @Route("/report")
 */
class ReportController extends Controller
{
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
        require_once __DIR__.'/../sfConfig.php';
        \sfConfig::fill($this->container->getParameter('experian'), 'global_experian');
        $this->netConnect->execute();
        return array();
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
