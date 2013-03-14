<?php
namespace CreditJeeves\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 * @Route("/report")
 */
class ReportController extends Controller
{
    /**
     * @Route("/get", name="core_report_get")
     * @Template()
     *
     * @return array
     */
    public function getAction()
    {
        return array();
    }
}
